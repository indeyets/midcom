<?php
/**
 * @package org.maemo.gforgeprofileupdater
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package org.maemo.gforgeprofileupdater
 */
class org_maemo_gforgeprofileupdater extends midcom_baseclasses_components_purecode
{
    var $_object = false;
    var $soap_url = false;
    var $soap_username = false;
    var $soap_password = false;

    var $_datamanager = null;
    var $_schema = null;

    var $_soap_client = false;
    var $_soap_fault = false;
    var $_soap_session_id = false;
    var $_soap_fields =  array
    (
        'username',
        'firstname',
        'lastname',
        'email',
    );

    function org_maemo_gforgeprofileupdater()
    {
        $this->_component = 'org.maemo.gforgeprofileupdater';
        parent::midcom_baseclasses_components_purecode();

        $this->soap_wdsl = $this->_config->get('soap_wdsl');
        $this->soap_username = $this->_config->get('soap_username');
        $this->soap_password = $this->_config->get('soap_password');

        return true;
    }

    /**
     * Initialized a SOAP session to gForge SOAP service
     *
     * @return boolean indicating success/failure
     */
    function initialize_soap()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        static $initialized = false;
        if ($initialized)
        {
            debug_add('Already initialized, returning early');
            debug_pop();
            return true;
        }
        // PEAR SOAP is not E_ALL compatible
        error_reporting(E_ALL & ~E_NOTICE);
        $this->_soap_client = new SOAP_Client($this->soap_wdsl, true);
        error_reporting(E_ALL);
        $c =& $this->_soap_client;
        $login_params = array
        (
            'userid' => $this->soap_username,
            'passwd' => $this->soap_password,
        );
        // PEAR SOAP is not E_ALL compatible
        error_reporting(E_ALL & ~E_NOTICE);
        $response = $c->call('login', $login_params);
        error_reporting(E_ALL);
        if (is_a($response, 'SOAP_Fault'))
        {
            debug_add('Got SOAP fault: ' . $response->getMessage(), MIDCOM_LOG_ERROR);
            debug_pop();
            $this->_soap_fault = $response;
            $this->_soap_client = false;
            return false;
        }

        $this->_soap_session_id = $response;
        $initialized = true;
        debug_add("Initialized with session id: {$this->_soap_session_id}", MIDCOM_LOG_INFO);
        debug_pop();
        return true;
    }

    function destroy_soap()
    {
        if (   !is_a($this->_soap_client, 'SOAP_Client')
            || !$this->initialize_soap())
        {
            // We don't have SOAP session open, return silently
            return true;
        }
        if ($this->call_gforge('logout', array()) != 'OK')
        {
            // Logout failed but there is not really anything we can do...
        }
        $this->_soap_session_id = false;
        $this->_soap_client = false;
    }

    /**
     * Calls GForge SOAP service, automatically prepends session id to parameters
     *
     * @param string $method remote method to call
     * @param array $params parameters to give to the method
     * @return mixed whatever the server returns or boolean false on error
     */
    function &call_gforge($method, $params)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (   $this->_soap_session_id === false
            && !$this->initialize_soap())
        {
            debug_add('Could not initialize GForge SOAP session');
            debug_pop();
            $x = false;
            return $x;
        }
        $c =& $this->_soap_client;
        $call_params = array_merge
        (
            array('session_ser' => $this->_soap_session_id),
            $params
        );
        // PEAR SOAP is not E_ALL compatible
        error_reporting(E_ALL & ~E_NOTICE);
        debug_add("Calling method '{$method}'");
        $response = $c->call($method, $call_params);
        error_reporting(E_ALL);
        if (is_a($response, 'SOAP_Fault'))
        {
            debug_add('Got SOAP fault: ' . $response->getMessage(), MIDCOM_LOG_ERROR);
            debug_print_r("Method '{$method}' params: ", $call_params, MIDCOM_LOG_INFO);
            debug_pop();
            $this->_soap_fault = $response;
            $x = false;
            return $x;
        }
        debug_print_r("Got response ", $response);
        $this->_soap_fault = false;
        debug_pop();
        return $response;
    }

    /**
     * Returns SOAP_Fault->getMessage() if last SOAP response was a fault, boolen false otherwise
     */
    function get_soap_error()
    {
        if (!is_a($this->_soap_fault, 'SOAP_Fault'))
        {
            return false;
        }
        return $this->_soap_fault->getMessage();
    }

    /**
     * Internal helper, loads the datamanager for the current object. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager($schemadb)
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($schemadb);

        if (!$this->_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for schemadb '{$schemadb}'");
            // This will exit.
        }
        if (!$this->_datamanager->set_schema($this->_schema))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed set_schema('{$this->_schema}')");
            // This will exit.
        }
    }

    function _load_schemadb()
    {
        $this->_schema = $this->_config->get('schema');
        $schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
        return $schemadb;
    }

    function _object_dm(&$object)
    {
        $schemadb = $this->_load_schemadb();
        $this->_load_datamanager($schemadb);
        return $this->_datamanager->set_storage($object);
    }

    /**
     * Communicate profile updates to gforge server
     *
     * @param mixed $object object
     */
    function updated(&$object)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!$this->_object_dm($object))
        {
            debug_add("Could not instantiate DM2 for object {$object->guid}", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        if (($gforge_user = $this->get_gforge_user($object->username)) === false)
        {
            debug_add("Could not get user '{$object->username}' from GForge, last SOAP error: " . $this->get_soap_error(), MIDCOM_LOG_ERROR);
            // Could not read user from GForge
            $this->destroy_soap();
            debug_pop();
            return false;
        }

        // Populate changed values from DM2
        $dm =& $this->_datamanager;
        $data_changed = false;
        // Can't foreach an object in all PHP4 versions, thus we use this approach
        while (list ($key, $value) = each($gforge_user))
        {
            if (   $key === 'user_id'
                || $key === 'user_name')
            {
                // Prevent people from shooting themselves to foot
                continue;
            }
            if (!isset($dm->types[$key]))
            {
                // Property not found in schema, skip
                continue;
            }
            if ($gforge_user->$key == $dm->types[$key]->value)
            {
                // This property is equal in both ends
                continue;
            }
            //debug_add("Changing \$gforge_user->{$key} from '{$gforge_user->$key}' to '{$dm->types[$key]->value}'");
            $gforge_user->$key = $dm->types[$key]->value;
            $data_changed = true;
        }

        if (!$data_changed)
        {
            debug_add('No data relevant for GForge has been changed, returning early');
            debug_pop();
            return true;
        }

        // Call the update method
        $call_params = array('userdata' => $gforge_user);
        if (($response = $this->call_gforge('updateUser', $call_params)) === false)
        {
            debug_add("Could not update user '{$object->username}' to GForge, last SOAP error: " . $this->get_soap_error(), MIDCOM_LOG_ERROR);
            $this->destroy_soap();
            debug_pop();
            return false;
        }
        debug_add("User '{$object->username}' successfully updated to GForge", MIDCOM_LOG_INFO);
        //debug_add("Response value: {$response}");

        $this->destroy_soap();
        debug_pop();
        return true;
    }

    /**
     * Fetches GForge SOAP User object by username
     *
     * @param string $username user name to fetch
     * @return object GForge SOAP 'tns:User' object or false on failure
     */
    function get_gforge_user($username)
    {
        $params = array('user_ids' => array($username));
        if (($response = $this->call_gforge('getUsersByName', $params)) === false)
        {
            return false;
        }
        return $response[0];
    }

    /**
     * Handle deleted user account
     */
    function deleted($object)
    {
        /* no-op ATM, in the future we could check
        if account exists in gforge and nuke it via SOAP but that's probably
        too risky */
        return true;
    }
}

?>