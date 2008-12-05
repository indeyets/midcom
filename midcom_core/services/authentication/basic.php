<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * HTTP Basic authentication service for MidCOM
 *
 * @package midcom_core
 */
class midcom_core_services_authentication_basic implements midcom_core_services_authentication
{
    private $user = null;
    private $person = null;
    private $sitegroup = null;
    
    public function __construct()
    {
        if (isset($_SERVER['PHP_AUTH_USER']))
        {
            $this->login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        }
    }
    
    public function is_user()
    {
        if (!$this->user)
        {
            return false;
        }
        
        return true;
    }
    
    public function get_person()
    {
        if (!$this->is_user())
        {
            return null;
        }
        
        if (is_null($this->person))
        {
            $this->person = new midgard_person($this->user->guid);
        }
        
        return $this->person;
    }
    
    public function get_user()
    {
        return $this->user;
    }

    public function login($username, $password)
    {
        if (extension_loaded('midgard2'))
        {
            // In Midgard2 we need current SG name for authentication
            $this->sitegroup = $_MIDGARD_CONNECTION->get_sitegroup();
        }
        
        $this->user = midgard_user::auth($username, $password, $this->sitegroup);
        
        if (!$this->user)
        {
            return false;
        }
        
        return true;
    }
    
    public function logout()
    {
        // TODO: Can this be implemented for Basic auth?
        return;
    }
    
    public function handle_exception(Exception $exception)
    {
        if (!isset($_SERVER['PHP_AUTH_USER']))
        {
            header("WWW-Authenticate: Basic realm=\"Midgard\"");
            header('HTTP/1.0 401 Unauthorized');
            // TODO: more fancy 401 output ?
            echo "<h1>Authorization required</h1>\n";
            exit();
        }
        
        if (!$this->login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']))
        {
            // Wrong password: Recurse until auth ok or user gives up
            unset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
            $this->handle_exception($exception);
        }
    }
}
?>