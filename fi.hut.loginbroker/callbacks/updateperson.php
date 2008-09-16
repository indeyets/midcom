<?php
/**
 * @package fi.hut.loginbroker
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */


/**
 * Simple user info update callback
 *
 * @package fi.hut.loginbroker
 */
class fi_hut_loginbroker_callbacks_updateperson extends fi_hut_loginbroker_callbacks_prototype_update
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
    function update($username, &$request_data, $iteration)
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

        $call_update = false;
        foreach ($this->data['property_map'] as $k => $v)
        {
            if (!property_exists($person, $k))
            {
                continue;
            }
            if ($person->$k == $v)
            {
                // property equals, no need to set
                continue;
            }
            $person->$k = $v;
            $call_update = true;
        }

        if (!$call_update)
        {
            // Others may find this useful...
            $this->data['person'] = $person;
            return true;
        }

        // get a fresh copy or references will mess up rollback...
        $this->_local_data['pre_update_person'] = $this->get_person_by_username($username);

        if (!$person->update())
        {
            unset($this->_local_data['pre_update_person']);
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
        if (!isset($this->_local_data['pre_update_person']))
        {
            debug_add('We have nothing to do, return early');
            debug_pop();
            return true;
        }
        $person =& $this->_local_data['pre_update_person'];
        if (!$person->update())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to update person, errstr: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_print_r('$person', $person);
            debug_pop();
            unset($this->_local_data['pre_update_person']);
            return false;
        }
        debug_add('Restored person to DB state before we touched it');
        unset($this->_local_data['pre_update_person']);
        
        debug_pop();
        return true;
    }
}


?>