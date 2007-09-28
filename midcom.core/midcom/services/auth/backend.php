<?php
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:backend.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Authentication backend, responsible for validating user/password pairs and
 * mapping them to a given user as well as the "sessioning" part, e.g. the transition
 * of the authentication credentials over several requests.
 * 
 * All functions except authenticate() must be implemented, see their individual 
 * documentation about what exactly they should do.
 * 
 * Configuration, if neccessary, should be done using the MidCOM configuration
 * system, prefixing all values with 'auth_backend_$name_', e.g. 
 * 'auth_backend_cookie_timeout'.
 * 
 * @package midcom.services
 */
class midcom_services_auth_backend extends midcom_baseclasses_core_object
{
    /**
     * This variable holds the user that has been successfully authenticated by the class,
     * it is considered to be read-only.
     * 
     * @var midcom_core_user
     */
    var $user = null;
    
    /**
     * The ID of the session we are currently using, usable as an authentication token
     * in the login session manager.
     * 
     * @var string
     */
    var $session_id = null;
    
    /**
     * The constructor should do only basic initialization.
     */
    function midcom_services_auth_backend ()
    {
        return parent::midcom_baseclasses_core_object();
    }

    /**
     * This function, always called first in the order of execution, should check
     * wether we have a usable login session. It has to use the login session management
     * system To load a login session. At the end of the successful execution of this 
     * function, you have to populate the $session_id and $user members accordingly.
     * 
     * @return bool Return true if the the login session was successfully loaded, false 
     *     otherwise.
     */    
    function read_login_session()
    {
        die(__CLASS__ . '::' . __FUNCTION__ . ' must be overridden.'); 
    }
    
    /**
     * This function checks the given username / password pair is valid and sets
     * the $user member accordingly. The default implementation checks against
     * mgd_auth_midgard and retrieves the user using $_MIDGARD.
     * 
     * Normally you should not need to override this function.
     * 
     * @param string $username The name of the user to authenticate.
     * @param string $password The password of the user to authenticate.
     * @return bool Indicating successful authentication.
     */
    function authenticate()
    {
        return $_MIDCOM->auth->sessionmgr->authenticate($this->_session_id);
    }

    /**
     * This function stores a login session using the given credentials through the
     * session service. It assumes that no login has concluded earlier. The login 
     * session management system is used for authentication. If the login session
     * was created successfully, the _on_login_session_created() handler is called
     * with the $user and $session_id members populated.
     * 
     * @param string $username The name of the user to authenticate.
     * @param string $password The password of the user to authenticate.
     * @param string $clientip The client IP to which this session is assigned to. This
     *     defaults to the client IP reported by Apache.
     * @return bool Indicating success.
     */
    function create_login_session($username, $password, $clientip = null)
    {
        if ($clientip === null)
        {
            $clientip = $_SERVER['REMOTE_ADDR'];
        }
        
        $result = $_MIDCOM->auth->sessionmgr->create_login_session($username, $password, $clientip);
        
        if (! $result)
        {
            // The callee will log errors at this point.
            return false;
        }
        
        $this->session_id = $result['session_id'];
        $this->user =& $result['user'];
        
        $this->_on_login_session_created();
        return true;
    }
    
    /**
     * This event handler is called immediately after the successful creation of a new login
     * session. The authentication driver has to ensure that the login identifier stays
     * available during subsequent requests.
     */
    function _on_login_session_created()
    {
        return;
    }
    
    /**
     * The logout function should delete the currently active login session,
     * which has been loaded by a previous call to read_login_session.
     * 
     * You should call generate_error if anything goes wrong here.
     */
    function logout()
    {
        if (   is_null($this->session_id)
            || ! $this->session_id)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('You were not logged in, so we do nothing.', MIDCOM_LOG_INFO);
            debug_pop();
            return;
        }

        if (! $_MIDCOM->auth->sessionmgr->delete_session($this->session_id))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 
                'The system could not log you out, check the log file for details.');
            // This will exit.
        }
        
        $this->_on_login_session_deleted();
        
        $this->session_id = null;
    }
     
     /**
      * This event handler is called immediately after the successful deletion of a login
      * session. Use this to drop any session identifier store you might have created during
      * _on_login_session_created.
      */
     function _on_login_session_deleted()
     {
         return;
     }
     
}

?>