<?php
/**
 * @package com_rohea_mjumpaccount
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Basic controller
 *
 * @package com_rohea_mjumpaccount
 *
 * Dependencies: com_rohea_authentication, com_rohea_images, com_rohea_uimessages
 */
class com_rohea_account_controllers_settings extends midcom_core_controllers_baseclasses_manage
{

    private $password_helper;
    private $account;
    private $current_user;

    public function __construct($instance)
    {
        $this->configuration = $instance->configuration;
        $this->password_helper = new com_rohea_account_passwordhelper();
    }
    
    public function load_object($args)
    {
        $this->object = $_MIDCOM->authentication->get_person();
    }
    
    public function prepare_new_object(&$data, $args)
    {
        $this->object = new midgard_person();
    }    
    
    public function get_url_edit()
    {}
    public function populate_toolbar()
    {}
    public function get_url_show()
    {}

    public function action_settings($route_id, &$data, $args)
    {
        $_MIDCOM->authorization->require_user();
        $this->current_user = $_MIDCOM->authentication->get_person();        

        $data['name'] = "com_rohea_account";
        $this->load_object($args); // Ollaan muokkaamassa olemassaolevaa
        $schemadb = $this->configuration->get('schemadb_settings');
        $this->load_datamanager($data, $schemadb, 'account_settings');
        $form = $this->datamanager->get_form();
        $data['form'] = $this->datamanager->get_form();        


        $lang_qb = new midgard_query_builder('midgard_language');
        $lang_qb->add_order('name');
        $languages_list = $lang_qb->execute();
        $data['languages_list'] = $languages_list;
        $data['person'] = $this->current_user;    

        $data['current_route_id'] = $route_id;
        // Koska kyseessä on käyttäjä, niin käyttäjän osalta pitää olla sudo päällä
        if($_MIDCOM->authentication->get_person()->guid == $this->object->guid)
        {
            $_MIDCOM->authorization->enter_sudo('com_rohea_account');
        }     
        //Doing some extra operations according to the spesific route
        if (isset($_MIDCOM->context->page->guid))
        {
            $data['page_guid'] = $_MIDCOM->context->page->guid;
        }

        $account = new com_rohea_account_account($this->current_user->guid);
                    
                        
        try
        {   
            $form->process();
        }
        catch(midcom_helper_datamanager_exception_save $e)
        {
            $url = $_MIDCOM->dispatcher->generate_url('settings', array());
            header('Location: '.$url);
            $_MIDCOM->authorization->leave_sudo();
            exit();
        }
        $_MIDCOM->authorization->leave_sudo();
    }
        
    public function action_uilanguage($route_id, &$data, $args)
    {
        $_MIDCOM->authorization->require_user();
        $this->current_user = $_MIDCOM->authentication->get_person();        

        $data['name'] = "com_rohea_account";
        $this->load_object($args); // Ollaan muokkaamassa olemassaolevaa
        $schemadb = $this->configuration->get('schemadb_settings');
        $this->load_datamanager($data, $schemadb, 'account_settings');
        $form = $this->datamanager->get_form();
        $data['form'] = $this->datamanager->get_form();        

        // Koska kyseessä on käyttäjä, niin käyttäjän osalta pitää olla sudo päällä
        if($_MIDCOM->authentication->get_person()->guid == $this->object->guid)
        {
            $_MIDCOM->authorization->enter_sudo('com_rohea_account');
        }       
        try
        {   
            $form->process();
        }
        catch(midcom_helper_datamanager_exception_save $e)
        {
            $translation = $_MIDCOM->authentication->find_translation($_MIDCOM->authentication->get_person()->uilanguage, null);
            $_MIDCOM->authentication->set_translation($translation);          
            $_MIDCOM->authentication->set_ui_language($_MIDCOM->authentication->get_person()->uilanguage);
            $url = $_MIDCOM->dispatcher->generate_url('settings_languages', array());
            $title_text = $_MIDCOM->i18n->get('key: Success', 'com_rohea_account');
            $message_text = $_MIDCOM->i18n->get('key: User interface language changed', 'com_rohea_account');
            $message = array('title' => $title_text, 'message' => $message_text, 'type' => 'ok');
            $_MIDCOM->uimessages->add($message);
            $_MIDCOM->uimessages->store();
            $_MIDCOM->authorization->leave_sudo();
            header('Location: '.$url);
            exit();
        }
        $lang_qb = new midgard_query_builder('midgard_language');
        $lang_qb->add_order('name');
        $languages_list = $lang_qb->execute();
        $data['languages_list'] = $languages_list;
        $data['person'] = $this->current_user;    
        //$data['current_route_id'] = $route_id;
        //NOTE. Requires com_rohea_authentication
        $ui_languages = $_MIDCOM->authentication->get_available_translations();
        //Persons current uilanguage
        $current_ui_language = $_MIDCOM->authentication->get_person()->uilanguage;        
        //Finding out the currently chosen ui language
        $i = 0;                
        foreach($ui_languages as $lang)
        {
            if ($lang->code == $current_ui_language)
            {
	          	$ui_languages[$i]->selected = true;
            }
            else
            {
                $ui_languages[$i]->selected = false;
            }
            $i++;
        }
        $data['post_url'] = $_MIDCOM->dispatcher->generate_url('settings_ui_language', array());        
        $data['current_ui_language'] = $current_ui_language;
        $data['ui_languages'] = $ui_languages;        
        $_MIDCOM->authorization->leave_sudo();    
    }    
    
    /**
      * Route is used for changing users's email address
      */
    public function action_email($route_id, &$data, $args)
    {
        $_MIDCOM->authorization->require_user();
        $this->current_user = $_MIDCOM->authentication->get_person();        

        // Loading datamanager for the current user
        $this->load_object($args); 
        $schemadb = $this->configuration->get('schemadb_settings_email');
        $this->load_datamanager($data, $schemadb, 'account_settings_email');
        $form = $this->datamanager->get_form();
        $data['form'] = $this->datamanager->get_form();        
        $data['person'] = $this->current_user;    
        $data['current_route_id'] = $route_id;
        
        // Entering sudo if logged person is same as we are editing
        // Done because ACL's are not finished
        if($_MIDCOM->authentication->get_person()->guid == $this->object->guid)
        {
            $_MIDCOM->authorization->enter_sudo('com_rohea_account');
        }     
        
        // Checking if modify form has been posted                    
        try
        {   
            $form->process();
        }
        catch(midcom_helper_datamanager_exception_save $e)
        {
            /*
             * Handling successfull datamanager post by assigning feedback
             * and redirecting
             */
            $title_text = $_MIDCOM->i18n->get('key: Success', 'com_rohea_account');
            $message_text = $_MIDCOM->i18n->get('key: E-mail address changed', 'com_rohea_account');
            $message = array('title' => $title_text, 'message' => $message_text, 'type' => 'ok');
            $_MIDCOM->uimessages->add($message);    
            $_MIDCOM->uimessages->store();    
            
            $url = $_MIDCOM->dispatcher->generate_url('settings', array());
            $_MIDCOM->authorization->leave_sudo();
            header('Location: '.$url);
            exit();
        }
        
        $_MIDCOM->authorization->leave_sudo();
    }

    /**
      * Route is used for changing users's additional information
      */
    public function action_userinfo($route_id, &$data, $args)
    {
        $_MIDCOM->authorization->require_user();
        $this->current_user = $_MIDCOM->authentication->get_person();        

        // Loading datamanager for the current user
        $this->load_object($args); 
        $schemadb = $this->configuration->get('schemadb_settings_userinfo');
        $this->load_datamanager($data, $schemadb, 'account_settings_userinfo');
        $form = $this->datamanager->get_form();
        $data['form'] = $this->datamanager->get_form();        
        $data['person'] = $this->current_user;    
        $data['current_route_id'] = $route_id;
        
        // Entering sudo if logged person is same as we are editing
        // Done because ACL's are not finished
        if($_MIDCOM->authentication->get_person()->guid == $this->object->guid)
        {
            $_MIDCOM->authorization->enter_sudo('com_rohea_account');
        }     
        
        // Checking if modify form has been posted                    
        try
        {   
            $form->process();
        }
        catch(midcom_helper_datamanager_exception_save $e)
        {
            /*
             * Handling successfull datamanager post by assigning feedback
             * and redirecting
             */
            $title_text = $_MIDCOM->i18n->get('key: Success', 'com_rohea_account');
            $message_text = $_MIDCOM->i18n->get('key: E-mail address changed', 'com_rohea_account');
            $message = array('title' => $title_text, 'message' => $message_text, 'type' => 'ok');
            $_MIDCOM->uimessages->add($message);    
            $_MIDCOM->uimessages->store();    
            
            $url = $_MIDCOM->dispatcher->generate_url('settings', array());
            $_MIDCOM->authorization->leave_sudo();
            header('Location: '.$url);
            exit();
        }
        
        $_MIDCOM->authorization->leave_sudo();
    }

    /**
      * Route action is used for changing user's password
      * NOTE: Datamanager is not used for passwords
      */
    public function action_password($route_id, &$data, $args)
    {
        $_MIDCOM->authorization->require_user();
        $this->current_user = $_MIDCOM->authentication->get_person();        

        // Loading Datamanager
        $this->load_object($args); 
        $schemadb = $this->configuration->get('schemadb_settings');
        $this->load_datamanager($data, $schemadb, 'account_settings');
        $form = $this->datamanager->get_form();
        $data['form'] = $this->datamanager->get_form();        
        
        // Because some oddities with ACL's checking if logged user is the one 
        // We are modifying.
        if($_MIDCOM->authentication->get_person()->guid == $this->object->guid)
        {
            $_MIDCOM->authorization->enter_sudo('com_rohea_account');
        }     
        
        $ok = false;
        $data['password_bad_quality'] = true;
        $data['password_similar'] = true;

        // Doing some sanity checks for passwords
        if(isset($_POST['pw']))
        {
            if ($this->password_helper->check_password_quality($_POST['pw'], $_POST['pw2']))
            {
                $ok = true;
                $data['password_bad_quality'] = false;
            }
            if ($this->password_helper->check_input_similarity($_POST['pw'], $_POST['pw2']))
            {
                $ok = true;
                $data['password_similar'] = false;
            }
        }
        
        // Checking if the entered old password is correct
        if( isset($_POST['oldpw']))
        {
            $ok = midgard_user::auth( $_MIDCOM->authentication->get_person()->username, $_POST['oldpw']);        
        }
        
        // If checks are passed and there's a new password around
        // Changing the password
        if ($ok && isset($_POST['pw']))
        {
            $user = $_MIDCOM->authentication->get_user();
            $person = $_MIDCOM->authentication->get_person();            

            // If change is successfull, updating login session
            if($user->password($person->username, $_POST['pw']) == true)
            {
                $_MIDCOM->authentication->update_login_session($_POST['pw']);       
            }
            
            // Adding feedback of success and doing a redirect
            $title_text = $_MIDCOM->i18n->get('com_rohea_account', 'key: Success');
            $message_text = $_MIDCOM->i18n->get('com_rohea_account', 'key: Password has been changed');
            $message = array('title' => $title_text, 'message' => $message_text, 'type' => 'ok');
            $_MIDCOM->uimessages->add($message);
            $_MIDCOM->uimessages->store();
      	    $url = $_MIDCOM->dispatcher->generate_url('settings', array());
            header('Location: ' . $url);
            exit();
        }
        else
        {
            // Password change was not a success. Redirect and feedback
            $message_text = $_MIDCOM->i18n->get("key: Password change failed", 'com_rohea_account');
            $message = array('title' => $_MIDCOM->i18n->get("key: Failure", 'com_rohea_account'), 'message' => $message_text, 'type' => 'ok');
            if (isset($_POST['pw']))
            {
                $_MIDCOM->uimessages->add($message);
                $_MIDCOM->uimessages->store();
                $url = $_MIDCOM->dispatcher->generate_url('settings', array());
                header('Location: ' . $url);
                exit();
            }
        }  

        $_MIDCOM->authorization->leave_sudo();
    }

    
    public function action_information($route_id, &$data, $args)
    {
        $_MIDCOM->authorization->require_user();
        $this->current_user = $_MIDCOM->authentication->get_person();        

        $this->load_object($args); // Ollaan muokkaamassa olemassaolevaa
        $schemadb = $this->configuration->get('schemadb_settings');
        $this->load_datamanager($data, $schemadb, 'account_settings');
        $form = $this->datamanager->get_form();
        $data['form'] = $this->datamanager->get_form();        
    }
   

    public function action_overview($route_id, &$data, $args)
    {
        $_MIDCOM->authorization->require_user();
        $this->current_user = $_MIDCOM->authentication->get_person();        

        $data['name'] = "com_rohea_account";
        $this->load_object($args); // Ollaan muokkaamassa olemassaolevaa
        $schemadb = $this->configuration->get('schemadb_settings');
        $this->load_datamanager($data, $schemadb, 'account_settings');
        $form = $this->datamanager->get_form();
        $data['form'] = $this->datamanager->get_form();
        $data['current_route_id'] = $route_id;
        //Doing some extra operations according to the spesific route
        if (isset($_MIDCOM->context->page->guid))
        {
            $data['page_guid'] = $_MIDCOM->context->page->guid;
        }
    }


    

}
?>
