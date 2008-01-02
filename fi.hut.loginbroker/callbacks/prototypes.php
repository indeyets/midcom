<?php
/**
 * @package ${module}
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Callbacks baseclass for storing common helper methods
 *
 * The midcom_baseclasses_components_purecode class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_purecode.html
 * 
 * @package fi.hut.loginbroker
 * @todo rollback system
 */
class fi_hut_loginbroker_callbacks_prototype_base extends midcom_baseclasses_components_purecode
{
    /**
     * Request data
     */
    var $data;

    /**
     * Local data storage for the plugin instance
     */
    var $_local_data = array();

    function fi_hut_loginbroker_callbacks_prototype_base()
    {
        $this->_component = 'fi.hut.loginbroker';
        parent::midcom_baseclasses_components_purecode();
    }

    /**
     * Rolls back changes made by this instance
     *
     * Subclasses must override this method
     */
    function rollback()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('This method must be overridden', MIDCOM_LOG_ERROR);
        debug_pop();
        return false;
    }

    /**
     * Helper to generate random string of printable characters of given lenght
     *
     * @param int $lenght how many characters?
     * @return string of random garbage
     */
    function generate_password($length=10)
    {
        static $rand = false;
        if (empty($rand))
        {
            if (function_exists('mt_rand'))
            {
                $rand = 'mt_rand';
            }
            else
            {
                $rand = 'rand';
            }
        }
        $ret = '';
        while($length--)
        {
            $ret .= chr($rand(33,125));
        }
        return $ret;
    }

    /**
     * Helper to get member object
     *
     * @param midcom_db_person $person person object
     * @param midcom_db_group $group group object
     * @return midcom_db_member object or false on failure
     */
    function get_member_object($person, $group)
    {
        $qb = midcom_db_member::new_query_builder();
        $qb->add_constraint('gid', '=', $group->id);
        $qb->add_constraint('uid', '=', $person->id);
        $result = $qb->execute();
        if (empty($result))
        {
            return false;
        }
        return $result[0];
    }

    /**
     * Get a fresh copy of a person object with given username
     *
     * Does some sanity checking (like fails on multiple persons with same username) 
     *
     * @param string $username user to fetch
     * @return midcom_db_person or false on failure
     */
    function get_person_by_username($username)
    {
        $qb = midcom_db_person::new_query_builder();
        $qb->add_constraint('username', '=', $username);
        $result = $qb->execute();
        if (empty($result))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not get person object for user '{$username}'", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;            
        }
        if (count($result) > 1)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Found more than one match for '{$username}'", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;            
        }
        $person = $result[0];
        unset($result);
        return $person;
    }

    /**
     * Is the given username taken
     *
     * Does not do any sanity checking, just tries to find the first
     * person with the username
     *
     * @param string $username username to check
     * @return boolean indicating state
     */
    function username_taken($username)
    {
        $mc = midcom_db_person::new_collector('username', $username);
        $mc->set_limit(1);
        $mc->execute();
        $keys = $mc->list_keys();
        return (bool)count($keys);
    }
}

/**
 * User creation callback baseclass
 *
 * @package fi.hut.loginbroker
 */
class fi_hut_loginbroker_callbacks_prototype_create extends fi_hut_loginbroker_callbacks_prototype_base
{
    function fi_hut_loginbroker_callbacks_prototype_create()
    {
        parent::fi_hut_loginbroker_callbacks_prototype_base();
    }

    /**
     * Operations related to creation of new user
     *
     * Once person has been created you must populate the person object to $request_data['person'] 
     * and password to $request_data['password']
     *
     * @param $username username to use
     * @param $request_data request data, see key 'property_map' for more interesting info
     * @param $iteration this is 1 if this is the first callback to be called, callbacks must check this and not create new person if this is > 1
     * @return boolean indicating success (do note that processing of callbacks etc is aborted if someone returns failure)
     */
    function create($username, &$request_data, $iteration)
    {
        $this->data =& $request_data;
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('This method must be overridden', MIDCOM_LOG_ERROR);
        debug_pop();
        return false;
    }
}

/**
 * User update callback baseclass
 *
 * @package fi.hut.loginbroker
 */
class fi_hut_loginbroker_callbacks_prototype_update extends fi_hut_loginbroker_callbacks_prototype_base
{
    function fi_hut_loginbroker_callbacks_prototype_update()
    {
        parent::fi_hut_loginbroker_callbacks_prototype_base();
    }

    /**
     * Operations related to updating an existing user
     *
     * @see fi_hut_loginbroker_callbacks_prototype_create::create()
     * @param $username username to use
     * @param $request_data request data, see key 'property_map' for more interesting info
     * @param $iteration this is 1 if this is the first callback to be called, callbacks must check this and not create new person if this is > 1
     */
    function update($username, &$request_data, $iteration)
    {
        $this->data =& $request_data;
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('This method must be overridden', MIDCOM_LOG_ERROR);
        debug_pop();
        return false;
    }
}

/**
 * User password reset callback baseclass
 *
 * @package fi.hut.loginbroker
 */
class fi_hut_loginbroker_callbacks_prototype_resetpasswd extends fi_hut_loginbroker_callbacks_prototype_base
{
    function fi_hut_loginbroker_callbacks_prototype_resetpasswd()
    {
        parent::fi_hut_loginbroker_callbacks_prototype_base();
    }

    /**
     * Reset the password of a given user
     *
     * @see fi_hut_loginbroker_callbacks_prototype_create::create()
     */
    function reset_passwd($username, &$request_data, $iteration)
    {
        $this->data =& $request_data;
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('This method must be overridden', MIDCOM_LOG_ERROR);
        debug_pop();
        return false;
    }
}

?>