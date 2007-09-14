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
     * Retvieve a reference to a device object, uses in-request caching
     *
     * @param $src string GUID of device (ids work but are discouraged)
     * @return org_maemo_devcodes_device_dba refence to device object or false
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
     * @return bool indicating sanity (true=sane)
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
     * Check if there are developer codes or applications for code for this device
     *
     * @return boolean indicating precense of dependencies
     */
    function has_dependencies()
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
        unset($qb);

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
        unset($qb);

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
        $qb = org_maemo_devcodes_device_dba::new_query_builder();
        $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        $devices = $qb->execute();
        foreach ($devices as $device)
        {
            if ($device->is_open())
            {
                // Not open for applications
                continue;
            }
            if (org_maemo_devcodes_application_dba::has_applied($device->id, $user))
            {
                // Given user has already applied
                continue;
            }
            $ret[$device->guid] = $device;
        }

        return $ret;
    }

    /**
     * Similar to is_open but does not check for close date
     *
     * @return bool indicating if we're past start datetime
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
        if ($this->end !== '0000-00-00 00:00:00')
        {
            $end_ts = strtotime($this->end);
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
     * @return bool indicating state
     */
    function is_open()
    {
        $start_ts = -1;
        $end_ts = -1;
        if ($this->start !== '0000-00-00 00:00:00')
        {
            $start_ts = strtotime($this->start);
        }
        if ($this->end !== '0000-00-00 00:00:00')
        {
            $end_ts = strtotime($this->end);
        }
        $now = time();

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
     * Check if given user can apply for this device
     *
     * @param int $user id of local user (defaults to $_MIDGARD['user'])
     * @param int $check_has_applied whether to check if user has already applied or not
     * @return bool indicating state or -1 on failure
     */
    function can_apply($user = -1, $check_has_applied = true)
    {
        return org_maemo_devcodes_application_dba::can_apply($this->id, $user, $check_has_applied);
    }
}

?>