<?php
/**
 * @package org.maemo.devcodes
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: aerodrome.php 3630 2006-06-19 10:03:59Z bergius $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapper class for device objects
 *
 * @package org.maemo.devcodes
 */
class org_maemo_devcodes_device_dba extends __org_maemo_devcodes_device_dba
{
    function org_maemo_devcodes_device_dba($src = null)
    {
        parent::__org_maemo_devcodes_device_dba($src);
    }

    /**
     * Retrieve a reference to a device object, uses in-request caching
     *
     * @param $src string GUID of device (ids work but are discouraged)
     * @return org_maemo_devcodes_device_dba reference to device object or false
     */
    function &get_cached($src)
    {
        $cache_name = '__org_maemo_devcodes_device_dba_get_cached_objects';
        if (!isset($GLOBALS[$cache_name]))
        {
            $GLOBALS[$cache_name] = array();
        }
        $cache =& $GLOBALS[$cache_name];
        if (isset($cache[$src]))
        {
            return $cache[$src];
        }
        $object = new org_maemo_devcodes_device_dba($src);
        if (   !$object
            && empty($object->guid))
        {
            $x = false;
            return $x;
        }
        $cache[$object->guid] = $object;
        $cache[$object->id] =& $cache[$object->guid];
        return $cache[$object->guid];
    }

    /**
     * Basic sanity checking for the code object
     *
     * @return boolean indicating sanity (true=sane)
     */
    function sanity_check()
    {
        if (!$this->can_do('org.maemo.devcodes:read'))
        {
            // If we don't have the above privilege we will have corrupted data...
            mgd_set_errno(MGD_ERR_ACCESS_DENIED);
            return false;
        }
        if (empty($this->codename))
        {
            return false;
        }
        if (!$this->codename_is_unique())
        {
            mgd_set_errno(MGD_ERR_DUPLICATE);
            return false;
        }
        return true;
    }

    function _on_loaded()
    {
        if (!$this->can_do('org.maemo.devcodes:read'))
        {
            if (!$this->has_opened())
            {
                // Allow generic read only for open programs
                mgd_set_errno(MGD_ERR_ACCESS_DENIED);
                return false;
            }
            // Clear some more sensitive values
            $this->codename = null;
            $this->notes = null;
        }
        return true;
    }

    function _on_creating()
    {
        return $this->sanity_check();
    }

    function _on_updating()
    {
        return $this->sanity_check();
    }

    function _on_deleting()
    {
        if ($this->has_dependencies())
        {
            mgd_set_errno(MGD_ERR_HAS_DEPENDANTS);
            return false;
        }
        return true;
    }

    function _on_updated()
    {
        // This will update the campaign titles as well...
        $this->create_smart_campaigns();
    }

    /**
     * Check if the codename we would like to use is still available
     *
     * @return boolean indicating state
     */
    function codename_is_unique()
    {
        $qb = org_maemo_devcodes_device_dba::new_query_builder();
        if ($this->id)
        {
            $qb->add_constraint('id', '<>', $this->id);
        }
        if ($this->node)
        {
            // If node is set check only inside said node
            $qb->add_constraint('node', '=', $this->node);
        }
        $qb->add_constraint('codename', '=', $this->codename);
        $qb->set_limit(1);
        // PONDER: should we use ACL aware count in stead, IMO we should disallow duplicate codes in all cases
        $count = $qb->count_unchecked();
        if ($count === false)
        {
            // Critical failure, return false to be safe
            return false;
        }
        if ($count > 0)
        {
            return false;
        }
        return true;
    }
    
    /**
     * Check if the given codename is still available
     *
     * @return boolean indicating state
     */
    function codename_is_unique_static($code, $node = null)
    {
        $handler = new org_maemo_devcodes_device_dba;
        $handler->codename = $code;
        if ($node !== null)
        {
            $handler->node = $node;
        }
        return $handler->codename_is_unique();
    }

    /**
     * Check if we have any "automatically" create smart campaigns for this device
     *
     * @see create_smart_campaigns
     * @return boolean indicating presence of campaigns
     */
    function _has_dependencies_check_campaigns()
    {
        $params = $this->list_parameters('org.maemo.devodes:autocreated_campaigns');
        if (!empty($params))
        {
            return true;
        }
        return false;
    }

    /**
     * Check if device has applications for it
     *
     * @return boolean indicating presence of applications
     */
    function _has_dependencies_check_applications()
    {
        $qb = org_maemo_devcodes_application_dba::new_query_builder();
        $qb->add_constraint('device', '=', $this->id);
        $applications_count = $qb->count_unchecked();
        if ($applications_count === false)
        {
            // fatal error, returning true just to be sure
            return true;
        }
        if ($applications_count > 0)
        {
            // we have applications for this device
            return true;
        }
        return false;
    }

    /**
     * Check if device has codes for it
     *
     * @return boolean indicating presence of codes
     */
    function _has_dependencies_check_codes()
    {
        $qb = org_maemo_devcodes_code_dba::new_query_builder();
        $qb->add_constraint('device', '=', $this->id);
        $codes_count = $qb->count_unchecked();
        if ($codes_count === false)
        {
            // fatal error, returning true just to be sure
            return true;
        }
        if ($codes_count > 0)
        {
            // we have codes linked to this device
            return true;
        }
        return false;
    }

    /**
     * Check if there are developer codes or applications for code for this device
     *
     * @return boolean indicating presence of dependencies
     */
    function has_dependencies()
    {
        $checks = array
        (
            'campaigns',
            'applications',
            'codes',
        );
        foreach ($checks as $check)
        {
            $method = "_has_dependencies_check_{$check}";
            if ($this->$method())
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Lists open device programs, uses in-request caching so calling this multiple times is not an issue
     *
     * @param $keyprop property to use as key
     * @param $valueprop property to use as value
     * @return array key/value pairs as set above
     */
    function list_open($keyprop = 'guid', $valueprop = 'title')
    {
        static $cache = array();
        $cache_key = "{$keyprop}-{$valueprop}";
        if (   isset($cache[$cache_key])
            && is_array($cache[$cache_key]))
        {
            return $cache[$cache_key];
        }
        else
        {
            $cache[$cache_key] = array();
        }
        $ret =& $cache[$cache_key];
        $ret = array();

        $mc = org_maemo_devcodes_device_dba::new_collector('sitegroup', $_MIDGARD['sitegroup']);
        $mc->add_constraint('start', '<=', date('Y-m-d H:i:s'));
        $mc->begin_group('OR');
            $mc->add_constraint('end', '>=', date('Y-m-d H:i:s'));
            $mc->add_constraint('end', '=', '0000-00-00 00:00:00');
        $mc->end_group();
        $mc->add_value_property($keyprop);
        $mc->add_value_property($valueprop);
        $mc->execute();
        $mc_keys = $mc->list_keys();
        if (!is_array($mc_keys))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('collector failed critically, returning empty resultset', MIDCOM_LOG_ERROR);
            debug_pop();
            return $ret;
        }
        foreach ($mc_keys as $guid => $empty)
        {
            $key = $mc->get_subkey($guid, $keyprop);
            $value = $mc->get_subkey($guid, $valueprop);
            $ret[$key] = $value;
        }
        return $ret;
    }

    /**
     * Lists all device programs, uses in-request caching so calling this multiple times is not an issue
     *
     * @param $keyprop property to use as key
     * @param $valueprop property to use as value
     * @return array key/value pairs as set above
     */
    function list_all($keyprop = 'guid', $valueprop = 'title')
    {
        static $cache = array();
        $cache_key = "{$keyprop}-{$valueprop}";
        if (   isset($cache[$cache_key])
            && is_array($cache[$cache_key]))
        {
            return $cache[$cache_key];
        }
        else
        {
            $cache[$cache_key] = array();
        }
        $ret =& $cache[$cache_key];
        $mc = org_maemo_devcodes_device_dba::new_collector('sitegroup', $_MIDGARD['sitegroup']);
        $mc->add_value_property($keyprop);
        $mc->add_value_property($valueprop);
        $mc->execute();
        $mc_keys = $mc->list_keys();
        if (!is_array($mc_keys))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('collector failed critically, returning empty resultset', MIDCOM_LOG_ERROR);
            debug_pop();
            return $ret;
        }
        foreach ($mc_keys as $guid => $empty)
        {
            $key = $mc->get_subkey($guid, $keyprop);
            $value = $mc->get_subkey($guid, $valueprop);
            $ret[$key] = $value;
        }

        return $ret;
    }

    /**
     * Lists all devices the given user can apply to, can be called statically
     *
     * @param int $user id of local user (defaults to $_MIDGARD['user'])
     * @return array keys are device guids, values device objects
     */
    function list_applicable($user = -1)
    {
        $ret = array();
        if ($user == -1)
        {
            $user = $_MIDGARD['user'];
        }
        if (!$user)
        {
            return $ret;
        }
        debug_push_class(__CLASS__, __FUNCTION__);
        $qb = org_maemo_devcodes_device_dba::new_query_builder();
        $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        $devices = $qb->execute();
        debug_add('Initially found ' . count($devices) . ' devices');
        foreach ($devices as $device)
        {
            if (!$device->is_open())
            {
                // Not open for applications
                debug_add("Device #{$device->id} is not open for applications, skipping");
                continue;
            }
            if (!org_maemo_devcodes_application_dba::can_apply($device->id, $user, true))
            {
                // Given user cannot apply for this device
                debug_add("User #{$user} cannot apply for device #{$device->id}, errstr: " . mgd_errstr());
                continue;
            }
            $ret[$device->guid] = $device;
        }

        debug_add('Returning ' . count($ret) . ' devices');
        debug_pop();
        return $ret;
    }

    /**
     * Similar to is_open but does not check for close date
     *
     * @return boolean indicating if we're past start datetime
     * @see is_open()
     */
    function has_opened()
    {
        $start_ts = -1;
        $end_ts = -1;
        if ($this->start !== '0000-00-00 00:00:00')
        {
            $start_ts = strtotime($this->start);
        }
        $now = time();

        if (   $now < $start_ts
            || $start_ts === -1)
        {
            return false;
        }
        return true;
    }

    /** 
     * Is this device program open for applications
     * 
     * @return boolean indicating state
     */
    function is_open()
    {
        $start_ts = -1;
        $end_ts = -1;
        if (   $this->start !== '0000-00-00 00:00:00'
            && !empty($this->start))
        {
            $start_ts = strtotime($this->start);
        }
        if (   $this->end !== '0000-00-00 00:00:00'
            && !empty($this->end))
        {
            $end_ts = strtotime($this->end);
        }
        $now = time();

        /*
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("\$this->start: {$this->start}, \$this->end: {$this->end}");
        debug_add("compares\n===\nstart: {$start_ts}\n  end: {$end_ts}\n  now: {$now}\n===\n");
        debug_pop();
        */

        if (   $now < $start_ts
            || $start_ts === -1)
        {
            return false;
        }
        if (   $end_ts !== -1
            && $now > $end_ts)
        {
            return false;
        }
        return true;
    }

    function get_parent_guid_uncached()
    {
        if (empty($this->node))
        {
            return null;
        }
        $parent = new midcom_db_topic($this->node);
        if (   !$parent
            || empty($parent->guid))
        {
            debug_push_class(__CLASS__, __FUNCTION);
            debug_add("Could not instantiate midcom_db_topic for node id #{$this->device}, device #{$this->id}", MIDCOM_LOG_WARN);
            debug_pop();
            return null;
        }
        return $parent->guid;
    }

    /**
     * Creates various useful smart campaigns related to this device
     *
     * @return boolean indicating success/failure
     */
    function create_smart_campaigns()
    {
        $_MIDCOM->componentloader->load_graceful('org.openpsa.directmarketing');
        if (!class_exists('org_openpsa_directmarketing_campaign'))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Could not load org.openpsa.directmarketing, aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $types = array
        (
            'accepted',
            'rejected',
            'assigned',
        );

        foreach($types as $type)
        {
            $method = "_create_smart_campaign_{$type}";
            if (!$this->$method())
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("\$this->{$method}() returned failure, aborting", MIDCOM_LOG_ERROR);
                debug_pop();
                /* We might not want to do this afterall
                $this->remove_smart_campaigns();
                */
                return false;
            }
        }
        return true;
    }

    /**
     * Fetch/create campaign object for given type of autocreated campaign
     *
     * @param string type to fetch/create for
     * @return reference to org_openpsa_directmarketing_campaign object
     */
    function &_create_smart_campaign_prepare_campaign_object($type)
    {
        $existing_guid = $this->get_parameter('org.maemo.devodes:autocreated_campaigns', $type);
        if (!empty($existing_guid))
        {
            $campaign = new org_openpsa_directmarketing_campaign($existing_guid);
            if (   $campaign
                || $campaign->guid == $existing->guid)
            {
                return $campaign;
            }
            else
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Got bad result when instantiating campaign {$existing_guid} (type: {$type}), creating new one", MIDCOM_LOG_ERROR);
                debug_print_r('got result: ', $campaign);
                debug_pop();
                unset($campaign);
            }
        }
        $_MIDCOM->auth->request_sudo();
        $campaign = new org_openpsa_directmarketing_campaign();
        $campaign->title = "newly created {$type} for device #{$this->id}";
        $campaign->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_CAMPAIGN_SMART;
        if (!$campaign->create())
        {
            $_MIDCOM->auth->drop_sudo();
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not create campaign, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_pop();
            $x = false;
            return $x;
        }
        $_MIDCOM->auth->drop_sudo();
        $this->set_parameter('org.maemo.devodes:autocreated_campaigns', $type, $campaign->guid);
        return $campaign;
    }

    /**
     * Helper to create a smart campaign for recipients of codes assigned for this device
     *
     * @see create_smart_campaigns
     * @return boolean indicating success/failure
     */
    function _create_smart_campaign_assigned()
    {
        $campaign =& $this->_create_smart_campaign_prepare_campaign_object('assigned');
        if (!$campaign)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('$this->_create_smart_campaign_prepare_campaign_object(\'assigned\') failed', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $campaign->rules = array
        (
            'type' => 'AND',
            'classes' => array
            (
                array
                (
                    'comment' => "Assigned codes for device \"{$this->title}\"",
                    'type'    => 'AND',
                    'class'   => 'org_maemo_devcodes_code_dba',
                    'rules'   => array
                    (
                        array
                        (
                            'property' => 'device',
                            'match'    => '=',
                            'value'    => $this->id,
                        ),
                        array
                        (
                            'property' => 'recipient',
                            'match'    => '<>',
                            'value'    => 0,
                        ),
                    ),
                ),
            ),
        );

        // TODO: localize
        $campaign->title = "Code recipients for {$this->title}";
        if (!$campaign->update())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to update campaign #{$campaign->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $campaign->schedule_update_smart_campaign_members();
        return true;
    }

    /**
     * Helper to create a smart campaign for applicants of accepted applications
     *
     * @see create_smart_campaigns
     * @return boolean indicating success/failure
     */
    function _create_smart_campaign_accepted()
    {
        $campaign =& $this->_create_smart_campaign_prepare_campaign_object('accepted');
        if (!$campaign)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('$this->_create_smart_campaign_prepare_campaign_object(\'accepted\') failed', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $campaign->rules = array
        (
            'type' => 'AND',
            'classes' => array
            (
                array
                (
                    'comment' => "Accepted applications for device \"{$this->title}\"",
                    'type'    => 'AND',
                    'class'   => 'org_maemo_devcodes_application_dba',
                    'rules'   => array
                    (
                        array
                        (
                            'property' => 'device',
                            'match'    => '=',
                            'value'    => $this->id,
                        ),
                        array
                        (
                            'property' => 'state',
                            'match'    => '=',
                            'value'    => ORG_MAEMO_DEVCODES_APPLICATION_ACCEPTED,
                        ),
                    ),
                ),
            ),
        );
        // TODO: localize
        $campaign->title = "Accepted applicants for {$this->title}";
        if (!$campaign->update())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to update campaign #{$campaign->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $campaign->schedule_update_smart_campaign_members();
        return true;
    }

    /**
     * Helper to create a smart campaign for applicants of rejected applications
     *
     * @see create_smart_campaigns
     * @return boolean indicating success/failure
     */
    function _create_smart_campaign_rejected()
    {
        $campaign =& $this->_create_smart_campaign_prepare_campaign_object('rejected');
        if (!$campaign)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('$this->_create_smart_campaign_prepare_campaign_object(\'rejected\') failed', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $campaign->rules = array
        (
            'type' => 'AND',
            'classes' => array
            (
                array
                (
                    'comment' => "Rejected applications for device \"{$this->title}\"",
                    'type'    => 'AND',
                    'class'   => 'org_maemo_devcodes_application_dba',
                    'rules'   => array
                    (
                        array
                        (
                            'property' => 'device',
                            'match'    => '=',
                            'value'    => $this->id,
                        ),
                        array
                        (
                            'property' => 'state',
                            'match'    => '=',
                            'value'    => ORG_MAEMO_DEVCODES_APPLICATION_REJECTED,
                        ),
                    ),
                ),
            ),
        );
        // TODO: localize
        $campaign->title = "Rejected applicants for {$this->title}";
        if (!$campaign->update())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to update campaign #{$campaign->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $campaign->schedule_update_smart_campaign_members();
        return true;

    }

    /**
     * Removes the "automatically" created smart-campaigns created with create_smart_campaigns
     *
     * @see create_smart_campaigns()
     * @return boolean indicating success/failure
     */
    function remove_smart_campaigns()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $_MIDCOM->componentloader->load_graceful('org.openpsa.directmarketing');
        if (!class_exists('org_openpsa_directmarketing_campaign'))
        {
            debug_add('Could not load org.openpsa.directmarketing, aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $params = $this->list_parameters('org.maemo.devodes:autocreated_campaigns');
        if (empty($params))
        {
            debug_pop();
            return true;
        }
        foreach ($params as $key => $guid)
        {
            $campaign = new org_openpsa_directmarketing_campaign($guid);
            if (   !$campaign
                || empty($campaign->guid))
            {
                if (mgd_errno() === MGD_ERR_OBJECT_DELETED)
                {
                    debug_add("Could not instantiate campaign {$guid}, it's already deleted, removing link", MIDCOM_LOG_INFO);
                    $this->delete_parameter('org.maemo.devodes:autocreated_campaigns', $key);
                    continue;
                }
                else
                {
                    debug_add("Could not instantiate campaign {$guid}, aborting", MIDCOM_LOG_ERROR);
                    debug_pop();
                    return false;
                }
                if (!$campaign->delete())
                {
                    debug_add("Could not delete campaign {$guid}, aborting", MIDCOM_LOG_ERROR);
                    debug_pop();
                    return false;
                }
                $this->delete_parameter('org.maemo.devodes:autocreated_campaigns', $key);
            }
        }
        debug_pop();
        return true;
    }

    /**
     * Check if given user can apply for this device
     *
     * @param int $user id of local user (defaults to $_MIDGARD['user'])
     * @param int $check_has_applied whether to check if user has already applied or not
     * @return boolean indicating state or -1 on failure
     */
    function can_apply($user = -1, $check_has_applied = true)
    {
        return org_maemo_devcodes_application_dba::can_apply($this->id, $user, $check_has_applied);
    }
}

?>