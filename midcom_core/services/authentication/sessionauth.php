<?php
/**
 * @package midcom_service_sessionauth
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Provides a session based authentication method.
 * Session and login data is stored to midcom_core_loginsession_db
 *
 * TODO: Refactoring is needed. Perhaps all more advanced authentication
 * methods should inherit the very basic authentication
 *
 * @package midcom_service_sessionauth
 */

class midcom_core_services_authentication_sessionauth implements midcom_core_services_authentication
{
    private $user = null;
    private $person = null;
    private $sitegroup = null;
    private $session_cookie = null;
    
    private $current_session_id = null;
    
    private $trusted_auth = false;
        
    public function __construct()
    {
        $this->session_cookie = new midcom_core_services_authentication_cookie();
        
        if ($this->session_cookie->read_login_session())
        {
            $sessionid = $this->session_cookie->get_session_id();
            $this->authenticate_session($sessionid);
        }
        elseif (   isset($_POST['username'])
                && isset($_POST['password']))
        {
            $this->login($_POST['username'], $_POST['password'], false);
        }

        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM authentication::session_read_and_authenticated');
        }
    }
    
    public function login($username, $password, $read_session = true)
    {
        if (   $read_session
            && $this->session_cookie->read_login_session())
        {
            $sessionid = $this->session_cookie->get_session_id();
            return $this->authenticate_session($sessionid);
        }
        return $this->create_login_session($username, $password);
    }
    
    public function trusted_login($username)
    {
        if ($this->session_cookie->read_login_session())
        {
            $sessionid = $this->session_cookie->get_session_id();
            return $this->authenticate_session($sessionid);
        }
        $this->trusted_auth = true;
        return $this->create_login_session($username, $password = '');
    }    
    
    public function is_user()
    {
        if (! $this->user)
        {
            return false;
        }
        
        return true;
    }
    
    public function get_person()
    {
        if (! $this->is_user())
        {
            return null;
        }
        
        if (is_null($this->person))
        {
            $this->person = new midgard_person($this->user->guid);
            $_MIDCOM->cache->register_object($this->person->guid);
        }
        return $this->person;
    }
    
    public function get_user()
    {
        return $this->user;
    }

    
    /**
     * Executes the login to midgard.
     * @param username
     * @param password
     * @return bool 
     */
    private function do_midgard_login($username, $password)
    {
        if (!$this->sitegroup)
        {
            // In Midgard2 we need current SG name for authentication
            $this->sitegroup = $_MIDGARD_CONNECTION->get_sitegroup();
        }
            
        if ($this->trusted_auth)
        {
            $this->user = midgard_user::auth($username, '', $this->sitegroup, true);
        }
        else
        {
            $this->user = midgard_user::auth($username, $password, $this->sitegroup);
        }

        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM authentication::do_midgard_login::midgard_auth_called');
        }

        // Don't allow trusted auth for admin users 
        if ($this->trusted_auth && !empty($this->user) && $this->user->is_admin())
        {
            // Re-check using password for admin users
            $this->user = midgard_user::auth($username, $password, $this->sitegroup, false);
        }

        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM authentication::do_midgard_login::midgard_auth_called');
        }
        
        if (! $this->user)
        {
            $this->session_cookie->delete_login_session_cookie();          
            return false;
        }
        
        return true;
    }
    
    /**
     * Function creates the login session entry to the database
     * TODO: Function does not produce any nice exceptions 
     *
     * @param username
     * @param password
     * @clientip determined automatically if not set
     */
    private function create_login_session($username, $password, $clientip = null)
    {
        $_MIDCOM->authorization->enter_sudo('midcom_core');    
    
        if (is_null($clientip))
        {
            $clientip = $_SERVER['REMOTE_ADDR'];
        }
        
        if (! $this->do_midgard_login($username, $password))
        {
            return false;
        }
        
        $session = new midcom_core_login_session_db();
        $session->userid = $this->user->guid;
        $session->username = $username;
        $session->password = $this->_obfuscate_password($password);
        $session->clientip = $clientip;
        $session->timestamp = time();
        $session->trusted = $this->trusted_auth; // for trusted authentication

        if (! $session->create())
        {
            // TODO: Add some exception?
            return false;
        }
        
        $result = array
        (
            'session_id' => $session->guid, 
            'user' => &$user // <-- FIXME: is this supposed to be $this->user instead?
        );
        
        $this->current_session_id = $session->guid;
        if (isset($_POST['remember_login']))
        {
            $this->session_cookie->create_login_session_cookie($session->guid, $this->user->guid, time() + 24 * 3600 * 365);
        }
        else
        {
            $this->session_cookie->create_login_session_cookie($session->guid, $this->user->guid);
        }

        $_MIDCOM->authorization->leave_sudo();         

        return $result;
    
    }
    
    /**
     * Function deletes login session row from database and
     * cleans away the cookie
     * TODO: Write the actual functionality
     */
    public function logout()
    {
        $qb = new midgard_query_builder('midcom_core_login_session_db');
        $qb->add_constraint('guid', '=', $this->session_cookie->get_session_id());
        $res = $qb->execute();
        $this->session_cookie->delete_login_session_cookie();
        if (! $res)
        {
            return false;
        }        
        $res[0]->delete();
        $res[0]->purge();
        $this->session_cookie = new midcom_core_services_authentication_cookie();        
        return true;
    }
    
    /**
     * This function authenticates a session that has been created 
     * previously with load_login_session (mandatory)
     * 
     * On success ... TODO: Write more
     *
     * If authentication fails, given session id will be deleted
     * from database immediately.
     *
     * @param string $sessionid The session identifier to authenticate against
     * @param bool Indicating success
     */
    public function authenticate_session($sessionid)
    {
        $qb = new midgard_query_builder('midcom_core_login_session_db');
        $qb->add_constraint('guid', '=', $sessionid);
        $res = $qb->execute();
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM authentication::authenticate_session::session_queried');
        }
        if (!$res)
        {
            $this->session_cookie->delete_login_session_cookie();
            return false;
        }
        $session = $res[0];

        $username = $session->username;
        $password = $this->_unobfuscate_password($session->password);
        $this->trusted_auth = $session->trusted;        

        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM authentication::authenticate_session::password_obfuscated');
        }
   
        if (! $this->do_midgard_login($username, $password))
        {
            if (! $session->delete())
            {
                // TODO: Throw exception
                // TODO: Sessions must be purged time to time
            }
            $this->session_cookie->delete_login_session_cookie();
            return false;
        }
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM authentication::authenticate_session::session_authenticated');
        }

        $this->current_session_id = $session->guid;
        return true;
    }
    
    public function update_login_session($new_password)
    {
        $pw = $this->_obfuscate_password($new_password);
        $session = new midcom_core_login_session_db($this->session_cookie->get_session_id());
        $session->password = $pw;
        $session->update();
    }    
    
    /**
     * This function obfuscates a password in some way so that accidential
     * "views" of a password in the database or a log are not immediately
     * a problem. This is not targeted to prevent intrusion, just to prevent
     * somebody viewing the logs or debugging the system is able to just
     * read somebody elses passwords (especially given that many users
     * share their passwords over multiple systems).
     *
     * _unobfuscate_password() is used to restore the password into its original
     * form.
     *
     * @param string $password The password to obfuscate.
     * @return string The obfuscated password.
     * @see _unobfuscate_password()
     * @access private
     */
    
    private function _obfuscate_password($password)
    {
        return base64_encode($password);
    }
    
    /**
     * Reverses password obfuscation.
     *
     * @param string $password The password to obfuscate.
     * @return string The obfuscated password.
     * @see _unobfuscate_password()
     * @access private
     */
    function _unobfuscate_password($password)
    {
        return base64_decode($password);
    }
    
    public function handle_exception(Exception $exception)
    {
        if (   isset($_POST['username']) 
            && isset($_POST['password']))
        {
            if ($this->login($_POST['username'], $_POST['password']))
            {
                $_MIDCOM->dispatcher->dispatch(); // TODO: is this dangerous? Removing it means error 500
            }
        }
        
        // Pass some data to the handler
        $data = array();
        $data['message'] = $exception->getMessage();
        $data['exception'] = $exception;
        $_MIDCOM->context->set_item('midcom_core_exceptionhandler', $data);
        
        // Set entry point and disable cache
        $_MIDCOM->context->set_item('template_entry_point', 'midcom-login-form');
        $_MIDCOM->context->set_item('cache_enabled', false);
        
        // Do normal templating
        $_MIDCOM->templating->template();
        $_MIDCOM->templating->display();
        exit();
    }

}

    
