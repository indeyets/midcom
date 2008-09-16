<?php
/**
 * @package ${module}
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */


/**
 * callback to map shibboleth affiliations to local group memberships
 *
 * @package fi.hut.loginbroker
 */
class fi_hut_loginbroker_callbacks_affiliations extends fi_hut_loginbroker_callbacks_prototype_base
{
    var $_local_config = array();

    function __construct()
    {
        parent::__construct();
        $this->_local_config = $this->_config->get('fi_hut_loginbroker_callbacks_affiliations_config');
        $this->_local_data['created_members'] = array();
        $this->_local_data['deleted_members'] = array();
    }

    /**
     * Proxy method when used as creation plugin
     */
    function create($username, &$request_data, $iteration)
    {
        if ($iteration === 1)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('This cannot operate as the first creation callback (we do not have user yet!), however we return true not to mess things up for others', MIDCOM_LOG_ERROR);
            debug_pop();
            return true;
        }
        return $this->update_memberships($username, $request_data, $iteration);
    }

    /**
     * Proxy method when used as update plugin
     */
    function update($username, &$request_data, $iteration)
    {
        return $this->update_memberships($username, $request_data, $iteration);
    }

    /**
     * Helper to sanity check the config and do some config related initializations
     */
    function _sanity_check_config()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $config =& $this->_local_config;
        $local_data =& $this->_local_data;
        if (!isset($config['enable']))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('"enable" key not present in config, invalid config array', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (!$config['enable'])
        {
            // plugin not enabled, we can skip the rest of checks
            return true;
        }
        debug_push_class(__CLASS__, __FUNCTION__);
        if (   !isset($config['map'])
            || !is_array($config['map']))
        {
            debug_add('"map" key not present in config (or not an array), must be array (which may be empty)', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (   !isset($config['header'])
            || empty($config['header']))
        {
            debug_add('"header" key not present in config (or empty), must point to a valid $_SERVER key', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        // Shibd might validly give null value which isset would not catch
        if (!array_key_exists($config['header'], $_SERVER))
        {
            debug_add("'header' key (value '{$config['header']}') does not point to a valid \$_SERVER key", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (   !isset($config['delimiter'])
            || empty($config['delimiter']))
        {
            debug_add('"delimiter" key not present in config (or empty), must have a value', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        // Make a handy array we can check against quickly
        $local_data['affiliations'] = array();
        $tmparr = explode($config['delimiter'], $_SERVER[$config['header']]);
        foreach ($tmparr as $affiliation)
        {
            $local_data['affiliations'][$affiliation] = true;
        }
        // All required config keys present
        debug_pop();
        return true;
    }

    function update_memberships($username, &$request_data, $iteration)
    {
        // Map some references
        $this->data =& $request_data;
        $local_data =& $this->_local_data;
        
        // Some sanity checks
        if (empty($username))
        {
            // in fact this should not happen
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('username is empty', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (!$this->_sanity_check_config())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('configuration is not sane, aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $config =& $this->_local_config;

        // See if we can shortcut
        if (!$config['enable'])
        {
            // not enabled, return with true since this is not an error condition
            return true;
        }
        if (empty($config['map']))
        {
            // map is empty, nothing to do, return with true since this is not an error condition
            return true;
        }

        // Get the person object we need
        if (   !isset($this->data['person'])
            || !is_a($this->data['person'], 'midcom_db_person')
            || $this->data['person']->username !== $username)
        {
            // We do not have usable person object in request data, try to find one
            $person = $this->get_person_by_username($username);
            if (!$person)
            {
                // method above reports errors
                return false;
            }
        }
        else
        {
            // Good person object in request data, use it
            $person = $this->data['person'];
        }

        // Start working
        foreach ($config['map'] as $affiliation => $guid)
        {
            // Fetch and sanity-check group
            $group = new midcom_db_group($guid);
            if (   !$group
                || !isset($group->guid)
                || empty($group->guid))
            {
                // non-existent group ?
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to fetch group {$guid}" . mgd_errstr(), MIDCOM_LOG_WARN);
                debug_pop();
                continue;
            }
            // Person does not have affiliation set but is member -> remove membership
            if (   !isset($local_data['affiliations'][$affiliation])
                && $group->is_member($person))
            {
                $member = $this->get_member_object($person, $group);
                if (!$member->delete())
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Failed to remove membership to group {$guid}, aborting and rolling back" . mgd_errstr(), MIDCOM_LOG_ERROR);
                    debug_pop();
                    $this->rollback();
                    return false;
                }
                $local_data['deleted_members'][] = $member;
                continue;
            }
            // Person sas affiliation set but is not yet member -> add membership
            if (   isset($local_data['affiliations'][$affiliation])
                && !$group->is_member($person))
            {
                if (!$group->add_member($person))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Failed to add as member to group {$guid}, aborting and rolling back" . mgd_errstr(), MIDCOM_LOG_ERROR);
                    debug_pop();
                    $this->rollback();
                    return false;
                }
                $local_data['created_members'][] = $this->get_member_object($person, $group);
                continue;
            }
            // We did not need to do anything...
        }

        // Others may find this usefull...
        $this->data['person'] = $person;
        return true;
    }

    /**
     * Rolls back the changes we have made
     *
     * Undeletes deleted member objects and deletes+purges created member objects
     */
    function rollback()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        // Sanity check first, then delete (and purge) created members
        if (   isset($this->_local_data['created_members'])
            && !empty($this->_local_data['created_members']))
        {
            $cnt = count($this->_local_data['created_members']);
            debug_add("We have created {$cnt} member objects, removing them");
            foreach ($this->_local_data['created_members'] as $k => $member)
            {
                if (!$member)
                {
                    // this may be false if things have previously gone bad, we ignore and continue
                    continue;
                }
                if (!$member->delete())
                {
                    debug_add("Failed to delete member #{$member->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                    // We don't abort here since we might be able to still do other things
                    continue;
                }
                debug_add("member #{$member->id} deleted, trying to purge as well");
                $member->purge();
                unset($this->_local_data['created_members'][$k]);
            }
            unset($this->_local_data['created_members']);
        }
        // Sanity check first, then undelete deleted members
        if (   isset($this->_local_data['deleted_members'])
            && !empty($this->_local_data['deleted_members']))
        {
            $cnt = count($this->_local_data['deleted_members']);
            debug_add("We have deleted {$cnt} member objects, undeleting them");
            foreach ($this->_local_data['deleted_members'] as $k => $member)
            {
                if (   !is_object($member)
                    || !isset($member->guid)
                    || empty($member->guid))
                {
                    // this may not be valid member object if things have previously gone bad, we ignore and continue
                    continue;
                }
                if (!midgard_member::undelete($member->guid))
                {
                    debug_add("Failed to undelete member #{$member->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                    // We don't abort here since we might be able to still do other things
                    continue;
                }
                debug_add("member #{$member->id} undeleted");
                unset($this->_local_data['deleted_members'][$k]);
            }
        }

        debug_pop();
        return true;
    }
}


?>