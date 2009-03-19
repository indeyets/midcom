<?php
/**
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 * 
 * @package com.rohea.facebook
 */
 
class com_rohea_facebook_controllers_facebookregistration extends midcom_core_controllers_baseclasses_manage
{
    
    var $is_logged = false; //is the user logged in or not?
    var $is_activated = false; //has the user already clicked email activation url    

    var $person;
    var $error = null;

    private $recaptcha;

    /**
     * Simple default constructor.
     */
    public function __construct($instance)
    {
        $this->configuration = $instance->configuration;

        if ($_MIDCOM->authentication->is_user())
        {
            $this->error = 'user_already_logged';
            return true;
        }
    }
    public function load_object($args)
    {
        $this->object = $_MIDCOM->authentication->get_person();
    }
    
    public function prepare_new_object($args)
    {
        $this->object = new com_rohea_account_db();
    }
    

    public function action_registration_redirect($route_id, &$data, $args)
    {}
    
    public function get_url_edit()
    {}
    public function populate_toolbar()
    {}
    public function get_url_show()
    {}

    
    public function action_registration($route_id, &$data, $args)
    {
        
        if($_MIDCOM->authentication->is_user())
        {
            $this->load_object($args);
            $schemadb = $this->configuration->get('schemadb_simpleregistration');
            $this->load_datamanager($data, $schemadb, 'account_registration');
        }
        else
        {  
            $this->prepare_new_object($args);        
            $schemadb = $this->configuration->get('schemadb_simpleregistration');
            $this->load_creation_datamanager($data, $schemadb, 'account_registration');
        }
        
        // load facebook class
        $api_key = trim($this->configuration->get("facebook_api_key"));
        $secret_key = trim($this->configuration->get("facebook_secret_key"));
        
        $data['api_key'] = $api_key;
        $data['facebook_receiver'] = trim($this->configuration->get("fb_receiver_file"));        
        
        $fb = new com_rohea_facebook_accounts($api_key, $secret_key);
        $fbid = $fb->getfacebookuser();
        
        /*  If cannot find facebook login information, redirect to login page */
        if (empty($fbid))
        {
            $redirect_url = $_MIDCOM->dispatcher->generate_url('login_content', array());
            header("Location: " . $redirect_url);
            exit();
        }
        
        $midgardperson = $fb->checkfacebooklink($fbid);
        
        // If a midgard_person is linked to the given facebookid, log him in
        if ($midgardperson != false)
        {
            // if user is logged in, redirect to homepage
            if($_MIDCOM->authentication->is_user())
            {
                
                $redirect_url = '/';
                
                header("Location: " . $redirect_url);
                exit();               
                
            }
            
            $fb->login_midgard_person($midgardperson);

            $redirect_url = '/';
  
            header("Location: " . $redirect_url);
            exit();
        }
        
        $data['newuser_url'] = $_MIDCOM->dispatcher->generate_url('registration', array());
        $data['olduser_url'] = $_MIDCOM->dispatcher->generate_url('connect_to_existing_user', array());        
        
        $facebook_details = $fb->get_userdetails_from_facebook($fbid);

        $data['registration_form'] = $this->datamanager->get_form();
        $data['keytest'] = 'key: testing';
        
        $qb = new midgard_query_builder('midgard_language');
        $qb->add_order('native');
        $data['languages'] = $qb->execute(); 
        $data['current_ui_language'] = 'fi'; 
        $data['current_primary_language'] = 'fi'; 
                
        $data['show_newuser_dialog'] = true;
        
        /*   If form is posted and existing midgard userid is being joined to a facebook id   */
        
        if (isset($_POST['com_rohea_facebook_linking']) && $_POST['com_rohea_facebook_linking'] == 'true')
        {
            $data['show_newuser_dialog'] = false;
            $_MIDCOM->authentication->login($_POST['username'], $_POST['pw']);
            $user = $_MIDCOM->authentication->get_user();
            if (!empty($user) && !empty($fbid))
            {
                $fb->addfacebooklink($fbid, $user->guid);
                header("Location: /");
                exit();
            }            
        }
        /*  Else, creating new midgard userid and joining facebook id to that  */
        else
        {
             
            try
            {
                $this->object->firstname = $facebook_details['first_name'];
                $this->object->lastname = $facebook_details['last_name'];
                
                $_MIDCOM->authorization->enter_sudo('com_rohea_facebook');

                $data['registration_form']->process();
                $_MIDCOM->authorization->leave_sudo();
                
            }
            catch(midcom_helper_datamanager_exception_save $e)
            {
                // We'll have to catch the password this way. There's some issues with datamanager
                $password = $_POST['pw'];

                // Doing trusted login with newly created user (we do not have password yet)                        
                $_MIDCOM->authentication->trusted_login($this->object->username);
                
                // After login setting password for the newly generated user via API
                $user = $_MIDCOM->authentication->get_user();
                $user->password($this->object->username, $password);
                
                // Password is now set, logging in with it
                $_MIDCOM->authentication->login($this->object->username, $password);
                $username = $this->object->username;
                
                /*  Link facebook id and midgard userid    */
                $fb->addfacebooklink($fbid, $user->guid);        

                $redirect_url = '/';
                
                header("Location: " . $redirect_url);
                exit();
            }
            
        
        if (isset($this->datamanager->validation_errors))
        {
            $data['validation_errors'] = $this->datamanager->validation_errors;
        }
                
        }
    }

    public function action_checkusername($route_id, &$data, $args)
    {
        $username = '';
        foreach( $_GET as $key => $val)
        {
            $username = $_GET[$key];
        }
        
        $qb = new midgard_query_builder('midgard_person');
        $qb->add_constraint('username', '=' , $username);
        $res = $qb->execute();
        if (count($res) == 0)
        {
            echo ('1');
            exit();
        }
        echo ('0');
        exit();
    }
        
    private function generate_link_to_component($component_name, $route_id, $args)
    {
        $qb = new midgard_query_builder('midgard_page');
        $qb->add_constraint('component', '=', $component_name);
        $res = $qb->execute();
        
        if (count($res) > 0)
        {
            return $_MIDCOM->dispatcher->generate_url($route_id, $args, $res[0]);
        }
        else return false;
    }
}
?>
