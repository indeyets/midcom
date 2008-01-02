<?php
/**
 * @package fi.hut.loginbroker
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is an URL handler class for fi.hut.loginbroker
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 * 
 * @package fi.hut.loginbroker
 */
class fi_hut_loginbroker_handler_index  extends midcom_baseclasses_components_handler 
{
    var $_broker_for = false;
    var $_redirect_to = false;
    var $_username = false;
    var $_password = false;
    var $_person = false;

    /**
     * Simple default constructor.
     */
    function fi_hut_loginbroker_handler_index()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * _on_initialize is called by midcom on creation of the handler. 
     */
    function _on_initialize()
    {
        $this->_request_data['broker_for'] =& $this->_broker_for;
        $this->_request_data['redirect_to'] =& $this->_redirect_to;
        $this->_request_data['username'] =& $this->_username;
        $this->_request_data['password'] =& $this->_password;
        $this->_request_data['person'] =& $this->_person;
        $this->_request_data['l10n'] =& $this->_l10n;
    }

    function cookies_domainpath($destsite)
    {
        // strip protocol from URL
        $destsite = preg_replace('%^.*?://%', '', $destsite);
        // strip port from url
        $destsite = preg_replace('%:[0-9]+/%', '/', $destsite);
        $domain = $destsite;
        $path = '/';
        if (strpos($destsite, '/') !== false)
        {
            list ($domain, $path) = explode('/', $destsite, 2);
            if (!preg_match('%^/%', $path))
            {
                $path = "/{$path}";
            }
        }
        return array($domain, $path);
    }

    function _check_broker_for(&$data)
    {
        if (   !isset($_REQUEST['broker_for'])
            || empty($_REQUEST['broker_for']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Site to broker for is not set');
            // This will exit
        }
        $data['broker_for'] = base64_decode($_REQUEST['broker_for']);
        
        if (   isset($_REQUEST['redirect_to'])
            && !empty($_REQUEST['redirect_to']))
        {
            $data['redirect_to'] = base64_decode($_REQUEST['redirect_to']);
        }
        if (empty($data['redirect_to']))
        {
            $data['redirect_to'] = $data['broker_for'];
        }
    }

    function _read_username(&$data)
    {
        $username_header = $this->_config->get('username_header');
        if (empty($username_header))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, '"username_header" is not configured');
            // This will exit
        }
        if (   !isset($_SERVER[$username_header])
            || empty($_SERVER[$username_header]))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not read username from \$_SERVER['{$username_header}']", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $data['username'] = $_SERVER[$username_header];
        return true;
    }

    function _do_callbacks($callbacks, $method, &$data)
    {
        $_MIDCOM->auth->request_sudo('fi.hut.logibroker');
        $this->_map_properties($data);
        $i = 0;
        $callback_objects = array();
        foreach ($callbacks as $callback_class)
        {
            ++$i;
            if (!fi_hut_loginbroker_viewer::load_callback_class($callback_class))
            {
                $msg = "Could not load callback class {$callback_class}";
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, $msg);
                // This will exit
            }
            $callback_objects[$i] = new $callback_class();
            $callback =& $callback_objects[$i];
            if (!$callback->$method($data['username'], $data, $i))
            {
                $msg = "Callback ({$callback_class}::{$method}) returned failure";
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add($msg, MIDCOM_LOG_ERROR);
                debug_print_r('property_map', $data['property_map'], MIDCOM_LOG_INFO);
                debug_pop();
                $rollback_objects = array_reverse($callback_objects);
                foreach ($rollback_objects as $rollback)
                {
                    $rollback->rollback();
                }
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, $msg);
                // This will exit
            }
        }
        $_MIDCOM->auth->drop_sudo();
    }

    function _check_user(&$data)
    {
        if (!$this->_read_username($data))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not read username');
            // This will exit
        }
        if (empty($data['username']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Empty username');
            // This will exit
        }
        // We need to use username as domain, thus direct collector
        $mc = new midgard_collector('midgard_person', 'username', $data['username']);
        $mc->set_key_property('username');
        $mc->add_value_property('password');
        $mc->add_value_property('guid');
        $mc->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        $mc->set_limit(1);
        $mc->execute();
        $keys = $mc->list_keys();
        if (empty($keys))
        {
            // User not found
            $callbacks = $this->_config->get('create_user_callbacks');
            if (   !$this->_config->get('allow_create_user')
                || empty($callbacks))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not find user '{$data['username']}'");
                // This will exit
            }
            /**
             * NOTE: if you change the 'if' above the meaning of this fall-through may change.
             * The following code must only be executed if 'allow_create_user' is true
             * AND 'create_user_callbacks' is not empty
             */
            $this->_do_callbacks($callbacks, 'create', &$data);
            if (!empty($data['password']))
            {
                return true;
            }
        }
        // user found
        $data['person'] = new midcom_db_person();
        $pwd_wsalt = $mc->get_subkey($data['username'], 'password');
        if (   substr($pwd_wsalt, 0, 2) === '**'
            && ($password = substr($pwd_wsalt, 2))
            && !empty($password))
        {
            // Cleartext password in DB, return early
            $data['password'] = $password;
            return true;
        }

        // Encrypted or empty cleartext ('**') password in DB
        if (!$this->_config->get('allow_reset_password'))
        {
            // Reset not allowed, abort
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not find valid local password to use and not allowed to automatically reset');
            // this will exit()
        }

        // Reset password
        $callbacks = $this->_config->get('reset_password_callbacks');
        $this->_do_callbacks($callbacks, 'reset_passwd', &$data);
        if (!empty($data['password']))
        {
            return true;
        }

        // If we fall this far something has gone wrong
        return false;
    }

    function _update_user(&$data)
    {
        if (!$this->_config->get('allow_update_user'))
        {
            return false;
        }
        $callbacks = $this->_config->get('update_user_callbacks');
        $this->_do_callbacks($callbacks, 'update', &$data);
        return true;
    }

    function _map_properties(&$data)
    {
        $map = $this->_config->get('property_map');
        if (!is_array($map))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Configured property map is not an array');
            // This will exit
        }
        $data['property_map'] = array();
        foreach ($map as $property => $server_key)
        {
            if (!isset($_SERVER[$server_key]))
            {
                continue;
            }
            $data['property_map'][$property] = $_SERVER[$server_key];
        }
    }


    /**
     * The handler for the index article. 
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     * 
     */
    function _handler_index ($handler_id, $args, &$data)
    {
        $_MIDCOM->cache->content->no_cache();
        if (!is_a($_MIDCOM->auth->_auth_backend, 'midcom_services_auth_backend_simple'))
        {
            // We can only operate with the default (and currently only) backend
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Loginbroker only supports the "simple" authentication backend');
            // this will exit()
        }
        $this->_check_broker_for($data);
        // We need sudo for just about everything from now on
        $_MIDCOM->auth->request_sudo('fi.hut.loginbroker');
        if (!$this->_check_user($data))
        {
            // The method should generate it's own errors but here we trap anyway
            return false;
        }
        $this->_update_user($data);
        if (!$this->_login_user($data))
        {
            // The method should generate it's own errors but here we trap anyway
            return false;
        }
        $_MIDCOM->auth->drop_sudo();

        // Muck session-id to redirect-url as GET if allowed
        if ($this->_config->get('allow_get_sessionid'))
        {
            if (strpos($data['redirect_to'], '?') === false)
            {
                $data['redirect_to'] .= '?';
            }
            else
            {
                $data['redirect_to'] .= '&';
            }
            $data['redirect_to'] .= rawurlencode($_MIDCOM->auth->_auth_backend->_cookie_id);
            $data['redirect_to'] .= '=' . rawurlencode("{$_MIDCOM->auth->_auth_backend->session_id}-{$_MIDCOM->auth->_auth_backend->user->id}");
        }

        $_MIDCOM->relocate($data['redirect_to']);
        // This should exit
        return true;
    }

    function _login_user(&$data)
    {
        if (   empty($data['username'])
            || empty($data['password']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not read username or password');
            // this will exit
        }
        list ($domain, $path) = $this->cookies_domainpath($data['broker_for']);
        $GLOBALS['midcom_config']['auth_backend_simple_cookie_path'] = $path;
        $GLOBALS['midcom_config']['auth_backend_simple_cookie_domain'] = $domain;
        if (!$_MIDCOM->auth->_auth_backend->create_login_session($data['username'], $data['password']))
        {
            // Login failed (how is this possible ??), what to do ?
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not log in with local credentials');
            // this will exit
        }

        $this->_store_headers();

        return true;
    }

    function _store_headers()
    {
        $store_headers = $this->_config->get('store_headers');
        if (   !is_array($store_headers)
            || empty($store_headers))
        {
            return;
        }
        // NOTE: this is not DBA object!
        $session_object = new midcom_core_login_session_db($_MIDCOM->auth->sessionmgr->current_session_id);
        foreach ($store_headers as $header)
        {
            if (!isset($_SERVER[$header]))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("\$_SERVER['{$header}'] is not set, cannot store", MIDCOM_LOG_WARN);
                debug_print_r('$_SERVER', $_SERVER);
                debug_pop();
                continue;
            }
            if (!$session_object->parameter('fi.hut.loginbroker:stored_headers', $header, $_SERVER[$header]))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("\$session_object->parameter('fi.hut.loginbroker:stored_headers', '{$header}', '{$_SERVER[$header]}') returned failure, errstr: " . mgd_errstr(), MIDCOM_LOG_WARN);
                debug_pop();
                continue;
            }
            // Do we want to do something after successful  store ??
        }
    }

    /**
     * This function does the output.
     *  
     */
    function _show_index($handler_id, &$data)
    {
        midcom_show_style('redirect-link');
    }
}
?>
