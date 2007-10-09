<?php

/**
 * This is a plugin for creatin a sitegroup
 */
class select_tkk_style extends midcom_baseclasses_components_handler
{
   /**
    * Simple constructor, which only initializes the parent constructor.
    */
    function select_tkk_style()
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
	            'handler' => array('select_tkk_style', 'select_style'),
	        ),
	    );
    }
    
    function _handler_select_style()
    {    
        $title = $this->_l10n->get('style selection');
        $_MIDCOM->set_pagetitle($title);
        
        if (   isset($_POST['tkk_sitewizard_style_submit'])   
            && !empty($_POST['tkk_sitewizard_style_submit'])
            && isset($_POST['tkk_sitewizard_style_select_template']) 
            && !empty($_POST['tkk_sitewizard_style_select_template']))
        {      
            $session = new midcom_service_session();
            
            if (!$session->exists("midgard_admin_wizards_koe"))
            {
                echo "HERE";
            }
            else
            {
                $host_creator = $session->get("midgard_admin_wizards_koe");
            }
            
            try
            {            
                $host_creator->set_host_style($_POST[tkk_sitewizard_style_select_template]);
                
                $session->set("midgard_admin_wizards_koe", $host_creator);
                
                $_MIDCOM->relocate($this->_request_data['next_plugin_full_path']);
            }
            catch (midgard_admin_sitewizard_exception $e)
            {
                $e->error();
                echo "WE SHOULD HANDLE THIS \n";
            }
        }
        elseif (   isset($_POST['tkk_sitewizard_style_submit'])   
                && !empty($_POST['tkk_sitewizard_style_submit']))
        {
            $_MIDCOM->uimessages->add(
                $this->_l10n->get('midcom.admin.wizards'),
                $this->_l10n->get('you need to select a style template')
            );
        }

        $qb = midcom_db_style::new_query_builder();
        $qb->add_constraint('name', 'LIKE', 'template_%');
        $qb->add_constraint('up', '=', 0);
        // TODO: Check for sitegroups?
        $this->_request_data['templates'] = $qb->execute();  
        
        return true;
    }
    
    function _show_select_style()
    {       
        midcom_show_style('tkk_sitewizard_style');
    }    
}

?>