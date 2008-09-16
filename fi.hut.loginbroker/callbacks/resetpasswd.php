<?php
/**
 * @package fi.hut.loginbroker
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */


/**
 * Simple password reset callback
 *
 * @package fi.hut.loginbroker
 */
class fi_hut_loginbroker_callbacks_resetpasswd extends fi_hut_loginbroker_callbacks_prototype_resetpasswd
{
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Creates new person and optionally adds to specific groups if requested to do so
     *
     * Populates the person object to $request_data['person'] 
     * and password to $request_data['password']
     *
     * @see fi_hut_loginbroker_callbacks_prototype_create::create()
     */
    function reset_passwd($username, &$request_data, $iteration)
    {
        if (empty($username))
        {
            // in fact this should not happen
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('username is empty', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $this->data =& $request_data;
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
        $this->_local_data['pre_update_password_encoded'] = $person->password;
        // Store a separate copy of the username, just in case...
        $this->_local_data['username'] = $username;
        $this->data['password'] = $this->generate_password();
        $person->password = "**{$this->data['password']}";

        if (!$person->update())
        {
            unset($this->_local_data['username'], $this->_local_data['pre_update_password_encoded']);
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to update person, errstr: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_print_r('$person', $person);
            debug_pop();
            return false;
        }
        // Others may find this useful...
        $this->data['person'] = $person;

        return true;
    }

    /**
     * Rolls back the changes we have made
     *
     * Calls update on the object we stored prior to making changes
     */
    function rollback()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!isset($this->_local_data['pre_update_password_encoded']))
        {
            debug_add('We have nothing to do, return early');
            debug_pop();
            return true;
        }
        if (!isset($this->_local_data['username']))
        {
            debug_add('We have old password but no username, very bad!', MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        $username =& $this->_local_data['username'];
        $person = $this->get_person_by_username($username);
        if (!$person)
        {
            debug_add("Could not find user '{$username}'", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $person->password = $this->_local_data['pre_update_password_encoded'];
        if (!$person->update())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to update person, errstr: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_print_r('$person', $person);
            debug_pop();
            unset($this->_local_data['pre_update_password_encoded']);
            return false;
        }
        debug_add("Restored old password to person #{$person->id}");
        unset($this->_local_data['username'], $this->_local_data['pre_update_password_encoded']);
        
        debug_pop();
        return true;
    }
}


?>