<?php
/**
 * @package org.maemo.devcodes
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: aerodrome.php 3630 2006-06-19 10:03:59Z bergius $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapper class for application objects
 *
 * @package org.maemo.devcodes
 */
class org_maemo_devcodes_application_dba extends __org_maemo_devcodes_application_dba
{
    var $title;

    function __construct($src = null)
    {
        parent::__construct($src);
    }

    /**
     * Find application that has certain code assigned
     *
     * @param int $code_id local id of code
     * @return object org_maemo_devcodes_application_dba that has given code set or false on failure
     */
    function get_by_code($code_id)
    {
        $qb = org_maemo_devcodes_application_dba::new_query_builder();
        $qb->add_constraint('code', '=', $code_id);
        $applications = $qb->execute();
        if (!isset($applications[0]))
        {
            return false;
        }
        return $applications[0];
    }

    function _on_loaded()
    {
        $device = org_maemo_devcodes_device_dba::get_cached($this->device);
        $this->title = "{$this->summary} for {$device->title}";
        if (!$this->state)
        {
            $this->state = ORG_MAEMO_DEVCODES_APPLICATION_PENDING;
        }
        return true;
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
     * Reject this application for a device 
     *
     * @return boolean indicating success/failure
     */
    function reject()
    {
        $device =& org_maemo_devcodes_device_dba::get_cached($this->device);
        if (!$device->can_do('org.maemo.devcodes:manage'))
        {
            mgd_set_errno(MGD_ERR_ACCESS_DENIED);
            return false;
        }
        if ($this->state === ORG_MAEMO_DEVCODES_APPLICATION_REJECTED)
        {
            // already accepted, return early
            mgd_set_errno(MGD_ERR_OK);
            return true;
        }
        if ($this->code)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Code assigned on to-be-rejected application, trying to un-assign. Triggered by #{$this->id}", MIDCOM_LOG_WARN);
            $code = new org_maemo_devcodes_code_dba($this->code);
            if ($code->recipient)
            {
                $code->recipient = 0;
                if (!$code->update())
                {
                    debug_add("Could not update code #{$code->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                }
            }
            debug_pop();
        }
        $this->state = ORG_MAEMO_DEVCODES_APPLICATION_REJECTED;

        // Do something else ??
        
        return $this->update();
    }

    /**
     * Accept this application for a device 
     *
     * @return boolean indicating success/failure
     */
    function accept()
    {
        $device =& org_maemo_devcodes_device_dba::get_cached($this->device);
        if (!$device->can_do('org.maemo.devcodes:manage'))
        {
            mgd_set_errno(MGD_ERR_ACCESS_DENIED);
            return false;
        }
        if ($this->state === ORG_MAEMO_DEVCODES_APPLICATION_ACCEPTED)
        {
            // already accepted, return early
            mgd_set_errno(MGD_ERR_OK);
            return true;
        }
        if (!$this->code)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("You must assign code before you can accept application. Triggered by id #{$this->id}", MIDCOM_LOG_ERROR);
            debug_pop();
            mgd_set_errno(MGD_ERR_ERROR);
            return false;
        }
        $code = new org_maemo_devcodes_code_dba($this->code);
        if ($code->recipient != $this->applicant)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Code (#{$code->id}) assigned for this application has no/different recipient (#{$code->recipient}) than applicant (#{$this->applicant}), setting to #{$this->applicant}. Triggered by #{$this->id}", MIDCOM_LOG_INFO);
            $code->recipient = $this->applicant;
            if (!$code->update())
            {
                debug_add("Could not update code #{$code->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            }
            debug_pop();
        }
        $this->state = ORG_MAEMO_DEVCODES_APPLICATION_ACCEPTED;

        // Do something else ??
        
        return $this->update();
    }

    /**
     * Basic sanity checking for the code object
     *
     * @return boolean indicating sanity (true=sane)
     */
    function sanity_check()
    {
        if (!$this->state)
        {
            $this->state = ORG_MAEMO_DEVCODES_APPLICATION_PENDING;
        }
        if (empty($this->applicant))
        {
            return false;
        }
        if (empty($this->device))
        {
            return false;
        }
        return true;
    }

    function _on_creating()
    {
        if (!$this->sanity_check())
        {
            // TODO: set better errnos in the method
            mgd_set_errno(MGD_ERR_ERROR);
            return false;
        }
        if (org_maemo_devcodes_application_dba::has_applied($this->device, $this->applicant))
        {
            mgd_set_errno(MGD_ERR_DUPLICATE);
            return false;
        }
        if (!org_maemo_devcodes_application_dba::can_apply($this->device, $this->applicant))
        {
            mgd_set_errno(MGD_ERR_ACCESS_DENIED);
            return false;
        }
        return true;
    }

    function _on_updating()
    {
        return $this->sanity_check();
    }

    function get_parent_guid_uncached()
    {
        $parent = new org_maemo_devcodes_device_dba($this->device);
        if (   !$parent
            || empty($parent->guid))
        {
            debug_push_class(__CLASS__, __FUNCTION);
            debug_add("Could not instantiate org_maemo_devcodes_device_dba for device id #{$this->device}, application #{$this->id}", MIDCOM_LOG_WARN);
            debug_pop();
            return null;
        }
        return $parent->guid;
    }

    /**
     * By default all authenticated users should be able to 
     * create applications but should not be able to read applications of others
     */
    function get_class_magic_default_privileges()
    {
        $privileges = parent::get_class_magic_default_privileges();
        $privileges['USERS']['midgard:create'] = MIDCOM_PRIVILEGE_ALLOW;
        $privileges['EVERYONE']['midgard:read'] = MIDCOM_PRIVILEGE_DENY;
        return $privileges;
    }

    /**
     * Check if given user can apply for given device, can be called statically
     *
     * @param int $device_id id of device to check
     * @param int $user id of local user (defaults to $_MIDGARD['user'])
     * @param int $check_has_applied whether to check if user has already applied or not
     * @return boolean indicating state or -1 on failure
     */
    function can_apply($device_id, $user = -1, $check_has_applied = true)
    {
        if ($user == -1)
        {
            $user = $_MIDGARD['user'];
        }
        if (!$user)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Could not resolver $user', MIDCOM_LOG_ERROR);
            debug_pop();
            return -1;
        }
        $person =& org_openpsa_contacts_person::get_cached($user);
        if ($person->get_parameter('org.maemo.devcodes', 'cannot_apply:all'))
        {
            // Generic 'cannot_apply' set
            mgd_set_errno(MGD_ERR_ACCESS_DENIED);
            return false;
        }
        $device =& org_maemo_devcodes_device_dba::get_cached($device_id);
        if ($person->get_parameter('org.maemo.devcodes', "cannot_apply:{$device->guid}"))
        {
            // Device specific 'cannot_apply' set
            mgd_set_errno(MGD_ERR_ACCESS_DENIED);
            return false;
        }
        if (   $check_has_applied
            && org_maemo_devcodes_application_dba::has_applied($device_id, $user))
        {
            // Already applied
            mgd_set_errno(MGD_ERR_DUPLICATE);
            return false;
        }
        return true;
    }

    /**
     * Check if given user has applied for given device, can be called statically
     *
     * @param int $device_id id of device to check
     * @param int $user id of local user (defaults to $_MIDGARD['user'])
     * @return boolean indicating state or -1 on failure
     */
    function has_applied($device_id, $user = -1)
    {
        if ($user == -1)
        {
            $user = $_MIDGARD['user'];
        }
        if (!$user)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Could not resolver $user', MIDCOM_LOG_ERROR);
            debug_pop();
            return -1;
        }
        $mc = org_maemo_devcodes_application_dba::new_collector('device', $device_id);
        $mc->add_constraint('applicant', '=', (int)$user);
        $mc->set_limit(1);
        $mc->execute();
        $keys = $mc->list_keys();
        if ($keys === false)
        {        
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Collector failed critically \$user: {$user}, \$device_id: {$device_id}", MIDCOM_LOG_ERROR);
            debug_pop();
            return -1;
        }
        $count = count($keys);

        if ($count > 0)
        {
            return true;
        }
        return false;
    }

    /**
     * Lists all devices the given user can apply to, can be called statically
     *
     * @param int $user id of local user (defaults to $_MIDGARD['user'])
     * @return array keys are device guids, values device objects
     */
    function list_applicable_devices($user = -1)
    {
        return org_maemo_devcodes_device_dba::list_applicable($user);
    }

    /**
     * Lists all applications user has made, can be called statically
     *
     * @param int $user id of local user (defaults to $_MIDGARD['user'])
     * @return array of application objects
     */
    function list_applications($user = -1)
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
        $qb = org_maemo_devcodes_application_dba::new_query_builder();
        $qb->add_constraint('applicant', '=', (int)$user);
        return $qb->execute();
    }

    /**
     * Check for existence of dependencies
     *
     * @return boolean indicating presence of dependencies
     */
    function has_dependencies()
    {
        if ($this->state !== ORG_MAEMO_DEVCODES_APPLICATION_PENDING)
        {
            // If the application is no longer pending then it should be considered its own dependency
            return true;
        }
        return false;
    }
}

?>