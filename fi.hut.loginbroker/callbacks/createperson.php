<?php
/**
 * @package fi.hut.loginbroker
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */


/**
 * Simple user creation callback
 *
 * @package fi.hut.loginbroker
 */
class fi_hut_loginbroker_callbacks_createperson extends fi_hut_loginbroker_callbacks_prototype_create
{
    function fi_hut_loginbroker_callbacks_createperson()
    {
        parent::fi_hut_loginbroker_callbacks_prototype_create();
    }

    /**
     * Check for account disabled via midcom_admin_user
     */
    function &_check_disabled_user($username)
    {
        $qb = midcom_db_person::new_query_builder();
        $qb->add_constraint('parameter.domain', '=', 'net.nehmer.account');
        $qb->add_constraint('parameter.name', '=', 'username');
        $qb->add_constraint('parameter.value', '=', $username);
        $result = $qb->execute();
        if (empty($result))
        {
            // no disabled user found, perfectly normal
            $x = false;
            return $x;            
        }
        if (count($result) > 1)
        {
            // This is kind of bad, we don't know which one to re-enable...
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Found more than one match for '{$username}'", MIDCOM_LOG_ERROR);
            debug_pop();
            $x = false;
            return $x;            
        }
        $person = $result[0];
        $this->data['password'] = $this->generate_password();
        $person->password = "**{$this->data['password']}";
        $person->username = $username;

        return $person;
    }

    /**
     * Creates new person and optionally adds to specific groups if requested to do so
     *
     * Populates the person object to $request_data['person'] 
     * and password to $request_data['password']
     *
     * @see fi_hut_loginbroker_callbacks_prototype_create::create()
     */
    function create($username, &$request_data, $iteration)
    {
        if (empty($username))
        {
            // in fact this should not happen
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('username is empty', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (!function_exists('property_exists'))
        {
            // we need this
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Function property_exists is needed by this method', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if ($iteration > 1)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('This can only operate as the first callback, however we return true not to mess things up for others', MIDCOM_LOG_ERROR);
            debug_pop();
            return true;
        }

        if ($this->username_taken($username))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("username '{$username}' already taken", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $this->data =& $request_data; 
        $config = $this->_config->get('fi_hut_loginbroker_callbacks_createperson_config');

        $disabled_person = $this->_check_disabled_user($username);
        if ($disabled_person !== false)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Found person #{$disabled_person->id} with disabled account '{$username}', trying to re-activate", MIDCOM_LOG_INFO);
            if (!$disabled_person->update())
            {
                unset($this->data['password']);
                debug_add('Failed to create person, errstr: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_print_r('$person', $person);
                debug_pop();
                return false;
            }
            $disabled_person->delete_parameter('net.nehmer.account', 'username');
            
            $this->data['person'] = $disabled_person;
            $this->_local_data['re-enabled_person_object'] = $disabled_person;
            debug_pop();
            return true;
        }


        $person = new midcom_db_person();
        foreach ($this->data['property_map'] as $k => $v)
        {
            if (!property_exists($person, $k))
            {
                continue;
            }
            $person->$k = $v;
        }
        $person->username = $username;
        $this->data['password'] = $this->generate_password();
        $person->password = "**{$this->data['password']}";

        if (!$person->create())
        {
            unset($this->data['password']);
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to create person, errstr: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_print_r('$person', $person);
            debug_pop();
            return false;
        }

        $this->_local_data['person_object'] = $person; // local copy intentionally
        $this->data['person'] = $person;
        
        if (   is_array($config)
            && isset($config['add_to_groups'])
            && is_array($config['add_to_groups'])
            && !empty($config['add_to_groups']))
        {
            $this->_local_data['member_objects'] = array();
            foreach ($config['add_to_groups'] as $guid)
            {
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
                if (!$group->add_member($person))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Failed to add as member to group {$guid}, aborting and rolling back" . mgd_errstr(), MIDCOM_LOG_ERROR);
                    debug_pop();
                    $this->rollback();
                    return false;
                }
                $this->_local_data['member_objects'][] = $this->get_member_object($person, $group);
            }
        }

        return true;
    }

    /**
     * Rolls back the changes we have made
     *
     * Deletes the person and member objects created
     */
    function rollback()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (isset($this->_local_data['re-enabled_person_object']))
        {
            $person =& $this->_local_data['re-enabled_person_object'];
            debug_add("Found re-enabled user #{$person->id}, disabling...");
            if (!$person->set_parameter('net.nehmer.account', 'username', $person->username))
            {
                debug_add('Could not store username in parameter, errstr: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            $person->username = '';
            $person->password = '';
            if (!$person->update())
            {
                debug_add("Could not update person #{$person->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            unset($this->_local_data['re-enabled_person_object']);
            debug_pop();
            return true;
        }
        if (!isset($this->_local_data['person_object']))
        {
            debug_add('We have nothing to do, return early');
            debug_pop();
            return true;
        }
        if (   isset($this->_local_data['member_objects'])
            && !empty($this->_local_data['member_objects']))
        {
            $cnt = count($this->_local_data['member_objects']);
            debug_add("We have created {$cnt} member objects, removing them");
            foreach ($this->_local_data['member_objects'] as $k => $member)
            {
                if (!$member)
                {
                    // this may be false if things have previously gone bad, we ignore and continue
                    continue;
                }
                if (!$member->delete())
                {
                    debug_add("Failed to delete member #{$member->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                    // We don't abort here since we might be able to still delete the person...
                    continue;
                }
                debug_add("member #{$member->id} deleted, trying to purge as well");
                $member->purge();
                unset($this->_local_data['member_objects'][$k]);
            }
            unset($this->_local_data['member_objects']);
        }
        $person =& $this->_local_data['person_object'];
        if (!$person->delete())
        {
            debug_add("Failed to delete person #{$person->id} ({$person->username}), errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        debug_add("Person #{$person->id} ({$person->username}) deleted, trying to purge as well");
        $person->purge();
        unset($person, $this->_local_data['person_object']);

        debug_pop();
        return true;
    }
}


?>