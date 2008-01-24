<?php
/**
 * @package org.maemo.devcodes
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: aerodrome.php 3630 2006-06-19 10:03:59Z bergius $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapper class for code objects
 *
 * @package org.maemo.devcodes
 */
class org_maemo_devcodes_code_dba extends __org_maemo_devcodes_code_dba
{
    var $title;

    function __construct($src = null)
    {
        parent::__construct($src);
    }

    function _on_loaded()
    {
        $device = org_maemo_devcodes_device_dba::get_cached($this->device);
        $this->title = "{$this->code} for {$device->title}";
        return true;
    }

    /**
     * Basic sanity checking for the code object
     *
     * @return boolean indicating sanity (true=sane)
     */
    function sanity_check()
    {
        $this->area = trim($this->area);
        if (empty($this->code))
        {
            return false;
        }
        if (empty($this->device))
        {
            return false;
        }
        if (!$this->code_is_unique())
        {
            mgd_set_errno(MGD_ERR_DUPLICATE);
            return false;
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

    /**
     * Check if the code we would like to use is still available
     *
     * @return boolean indicating state
     */
    function code_is_unique()
    {
        $qb = org_maemo_devcodes_code_dba::new_query_builder();
        if ($this->id)
        {
            $qb->add_constraint('id', '<>', $this->id);
        }
        $qb->add_constraint('code', '=', $this->code);
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
     * Check if the given code is still available
     *
     * @return boolean indicating state
     */
    function code_is_unique_static($code)
    {
        $handler = new org_maemo_devcodes_code_dba;
        $handler->code = $code;
        return $handler->code_is_unique();
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
     * Check if this code is assigned to someone
     *
     * @return boolean indicating presence of dependencies
     */
    function has_dependencies()
    {
        if (!empty($this->recipient))
        {
            return true;
        }
        // TODO: check for applications linking to this code, though then the recipient should always be set as well
        return false;
    }

    function get_parent_guid_uncached()
    {
        $parent = new org_maemo_devcodes_device_dba($this->device);
        if (   !$parent
            || empty($parent->guid))
        {
            debug_push_class(__CLASS__, __FUNCTION);
            debug_add("Could not instantiate org_maemo_devcodes_device_dba for device id #{$this->device}, code #{$this->id}", MIDCOM_LOG_WARN);
            debug_pop();
            return null;
        }
        return $parent->guid;
    }

    /**
     * By default all authenticated users should be able to 
     * create applications
     */
    function get_class_magic_default_privileges()
    {
        $privileges = parent::get_class_magic_default_privileges();
        $privileges['EVERYONE']['midgard:read'] = MIDCOM_PRIVILEGE_DENY;
        return $privileges;
    }
}

?>