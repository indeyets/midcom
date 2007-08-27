<?php
/**
 * @package net.nemein.openid 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
 
/**
 * Require the OpenID consumer code.
 */
require_once "Auth/OpenID/Consumer.php";

/**
 * Require the "file store" module, which we'll need to store OpenID
 * information.
 */
require_once "Auth/OpenID/FileStore.php";
 
/**
 * This class uses the Auth_OpenID methods for performing an OpenID authentication
 *
 * @package net.nemein.openid
 */
class net_nemein_openid_consumer extends midcom_baseclasses_components_purecode
{
    /**
     * This is where we will store the OpenID information.
     */
    private $path = '/tmp/_midcom_openid_consumer';
    
    private $store = null;
    
    private $consumer = null;
    
    private $response = null;
    
    private $session = null;
    
    protected $openid = '';

    /**
     * Constructor
     */
    public function __construct($path = null)
    {
        if (!is_null($path))
        {
            $this->path = $path;
        }
        
        $this->_component = 'net.nemein.openid';
        
        parent::midcom_baseclasses_components_purecode();
        
        $this->initialize();
    }
    
    private function initialize()
    {
        if (   !file_exists($this->path) 
            && !mkdir($this->path))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not create the OpenID FileStore directory '{$this->path}'. Please check the effective permissions.");
            // This will exit
        }
        
        if (!is_writable($this->path))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "OpenID consumer cannot write to '{$this->path}'. Please check the effective permissions.");
            // This will exit
        }
        
        // Start-up session
        $this->session = new midcom_service_session('midcom_services_uimessages');
        
        $this->store = new Auth_OpenID_FileStore($this->path);
        
        /**
         * Create a consumer object using the store object created earlier.
         */
        $this->consumer = new Auth_OpenID_Consumer($this->store, $this->session);
    }
    
    public function begin($openid)
    {
        return $this->consumer->begin($openid);
    }
    
    public function complete($query)
    {
        $this->response = $this->consumer->complete($query);
        
        if ($this->response->status == Auth_OpenID_SUCCESS)
        {
            $this->openid = $this->response->identity_url;
        }
        return $this->response;
    }
    
    public function authenticate()
    {
        if (   !isset($this->response->status)
            || $this->response->status != Auth_OpenID_SUCCESS)
        {
            return false;
        }
        
        // Enter SUDO mode
        if (!$_MIDCOM->auth->request_sudo('net.nemein.openid'))
        {
            return false;
        }
        
        $qb = midcom_db_person::new_query_builder();
        $qb->add_constraint('username', '=', $this->openid);
        $qb->add_constraint('password', '<>', '');
        $persons = $qb->execute();
        if (empty($persons))
        {
            // Try to autoregister new user
            $person = $this->register();
            if (is_null($person))
            {
                return false;
            }
        }
        else
        {
            // Use existing account
            $person = $persons[0];
        }
        $password = substr($person->password, 2);
        
        // Return from SUDO mode as we are now going to authenticate normally
        $_MIDCOM->auth->drop_sudo();
        
        if (!$_MIDCOM->auth->login($this->openid, $password))
        {
            die($password);
            return false;
        }
        
        return true;
    }
    
    /**
     * Password generation algorithm. Copied from net.nemein.personne
     */
    private function generate_password($length = 20)
    {
        $similars = array
        (
            'I', 'l', '1', '0', 'O',
        );
        $no_similars = true;
        $strong = true;
        
        $string = '';
        
        for ($x = 0; $x < $length; $x++)
        {
            $rand = (int) rand(48, 122);
            $char = chr($rand);
            
            $k = 0;
            
            while (   !ereg('[a-zA-Z0-9]', $char)
                   || (   $strong
                       && strlen($string) > 0
                       && strstr($string, $char))
                   || (   $no_similars
                       && in_array($char, $similars)))
            {
                $rand = (int) rand(48, 122);
                $char = chr($rand);
                
                $k++;
            }
            
            $string .= $char;
        }
        
        return $string;
    }
    
    private function register()
    {
        if (!$this->_config->get('autoregistration_enable'))
        {
            return null;
        }
    
        // Ensure the user really does not exist
        $qb = midcom_db_person::new_query_builder();
        $qb->add_constraint('username', '=', $this->openid);
        $qb->add_constraint('password', '<>', '');
        if ($qb->count() != 0)
        {
            // TODO: Exception
            return null;
        }
        
        // Create the person
        $person = new midcom_db_person();
        $person->username = $this->openid;
        $person->homepage = $this->openid;
        
        // Store generated random password as plaintext so we can read it for future logins
        // TODO: In Trusted Auth mode we don't need passwords
        $person->password = '**' . $this->generate_password();
        
        if (!$person->create())
        {
            return null;
        }
        
        // Store information about OpenID processing
        $person->parameter('net.nemein.openid', 'autoregistration_time', date('r'));
        $person->parameter('net.nemein.openid', 'autoregistration_ip', $_SERVER['REMOTE_ADDR']);
        
        // TODO: Datamanager registration handling here
        
        return $person;
    }
}
?>