<?php
/**
 * @package com.rohea.mjumpaccount
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is an URL handler class for com.rohea.mjumpaccount
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 * 
 * Controller for user registration
 *
 * @package com.rohea.account
 */
class com_rohea_account_controllers_registration extends midcom_core_controllers_baseclasses_manage
{
    
    var $is_logged = false; //is the user logged in or not?
    
    var $person;
    var $error = null;

    private $recaptcha;
        
    /**
     * Simple default constructor.
     */
    public function __construct($instance)
    {
        $this->configuration = $instance->configuration;
                    
        //Check if user is already logged in
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
            $url = $_MIDCOM->dispatcher->generate_url('settings', array());
            header('Location: ' . $url);
            exit();
        }
        
        
        // Preparing new objects for registration
        $this->prepare_new_object($args);        
        $schemadb = $this->configuration->get('schemadb_registration');
        $this->load_creation_datamanager($data, $schemadb, 'account_registration');
        
        // Getting form for TAL
        $data['registration_form'] = $this->datamanager->get_form();

        // Getting possible UI languages
        $qb = new midgard_query_builder('midgard_language');
        $qb->add_order('native');
        $qb->add_constraint('code', 'IN', array('fi', 'sv'));
        $data['current_ui_language'] = '';
        $data['translated_languages'] = $qb->execute();
        
        
        $data['languages'] = $qb->execute(); //$_MIDCOM->authentication->get_available_translations();
        try
        {
            $_MIDCOM->authorization->enter_sudo('com_rohea_account');
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
            
            header("Location: /");
            exit();
        }

        if (isset($this->datamanager->validation_errors))
        {
            $data['validation_errors'] = $this->datamanager->validation_errors;
        }
                
    }

    public function action_checkusername($route_id, &$data, $args)
    {
        $username = '';
        foreach( $_GET as $key => $val)
        {
            $username = $_GET[$key];
        }
        $username = trim($username);
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
}
?>
