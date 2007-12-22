<?php

/**
 * This is a plugin for creating a host
 */
class default_create_host extends midcom_baseclasses_components_handler
{
   /**
    * Simple constructor, which only initializes the parent constructor.
    */
    function default_create_host()
    {
	    parent::midcom_baseclasses_components_handler();
    }

    function _on_initialize()
    {
        if (   isset($this->_request_data['plugin_config']['sitewizard_path'])
            && !empty($this->_request_data['plugin_config']['sitewizard_path']))
        {
            require_once($this->_request_data['plugin_config']['sitewizard_path']);
        }
        else
        {
            $_MIDCOM->uimessages->add(
                $this->_l10n->get('midcom.admin.wizards'),
                $this->_l10n->get('sitewizard was not found')
            );
            $_MIDCOM->relocate('');
        }

        parent::_on_initialize();
        
      }

    function get_plugin_handlers()
    {
        return array
        (
	        'sitewizard' => array
	        (
	            'handler' => array('default_create_host', 'create_host'),
	        ),
	    );
    }
    
    function _handler_create_host()
    {
        $title = $this->_l10n->get('host creation');
        $_MIDCOM->set_pagetitle($title);
    
        if (   isset($_POST['default_sitewizard_sitename'])   
            && !empty($_POST['default_sitewizard_sitename'])
            && isset($_POST['default_sitewizard_host'])  
            && !empty($_POST['default_sitewizard_host']))
        {      
            try
            {
                $session = new midcom_service_session();
                $sitegroup_creator = $session->get("midgard_admin_wizards_{$this->_request_data['session_id']}");
                $host_creator = $sitegroup_creator->next_link();            
                
                $host_creator->set_page_title($_POST['default_sitewizard_sitename']);
                $host_creator->set_host_url($_POST['default_sitewizard_host']);
                
                if (    isset($_POST['default_sitewizard_prefix'])   
                    &&  !empty($_POST['default_sitewizard_prefix']))
                {
                    $host_creator->set_host_prefix($_POST['default_sitewizard_prefix']);
                }
                
                $host_creator->set_host_port(80);
                
                $host_creator->set_make_host_copy(true);
                $host_creator->set_copy_host_url($_POST['tkk_sitewizard_host']);
                $host_creator->set_copy_host_port($this->_request_data['plugin_config']['copy_host_port']);
                
                $session->set("midgard_admin_wizards_{$this->_request_data['session_id']}", $host_creator);
                
                $_MIDCOM->relocate($this->_request_data['next_plugin_full_path']);
            }
            catch (midgard_admin_sitewizard_exception $e)
            {
                $e->error();
                echo "WE SHOULD HANDLE THIS \n";
            }
        }
        elseif (   isset($_POST['tkk_sitewizard_host_submit'])  
                && !empty($_POST['tkk_sitewizard_host_submit']))
        {
            $_MIDCOM->uimessages->add(
                $this->_l10n->get('midcom.admin.wizards'),
                $this->_l10n->get('you need to fill in both fields')
            );
        }
        
        $this->_request_data['current_host'] = new midcom_db_host($_MIDGARD['host']);
        
        return true;
    }
    
    function _show_create_host()
    {        
        midcom_show_style('default_sitewizard_host');
    }    
}

?>

