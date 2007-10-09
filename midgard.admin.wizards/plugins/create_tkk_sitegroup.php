<?php


/**
 * This is a plugin for creatin a sitegroup
 */
class create_tkk_sitegroup extends midcom_baseclasses_components_handler
{


   /**
    * Simple constructor, which only initializes the parent constructor.
    */
    function create_tkk_sitegroup()
    {
	    parent::midcom_baseclasses_components_handler();
    }

    function _on_initialize()
    {
        if (   isset($this->_request_data['plugin_config']['sitewizard_path'])
            && !empty($this->_request_data['plugin_config']['default_sitegroup_id']))
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
	            'handler' => array('create_tkk_sitegroup', 'create_sitegroup'),
	        ),
	    );
    }
    
    function _handler_create_sitegroup()
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
    
        $title = $this->_l10n->get('sitegroup creation');
        $_MIDCOM->set_pagetitle($title);
        
        if (isset($_POST))
        print_r($_POST);
        
        if (   isset($_POST['tkk_sitewizard_hostname'])   && !empty($_POST['tkk_sitewizard_hostname'])
            && isset($_POST['tkk_sitewizard_adminuser'])  && !empty($_POST['tkk_sitewizard_adminuser'])
            && isset($_POST['tkk_sitewizard_adminpass'])  && !empty($_POST['tkk_sitewizard_adminpass']))
        {      
            try
            {
                $sitewizard = new midgard_admin_sitewizard();
                $sitewizard->set_verbose(true);
                
                // EI N€IN
                $_MIDCOM->relocate($prefix . "tkk_sitewizard/create_tkk_host/");
            }
            catch (midgard_admin_sitewizard_exception $e)
            {
                $e->error();
                echo "WE SHOULD HANDLE THIS \n";
            }
        }
        else
        {
        
        }
        
        return true;
    }
    
    function _show_create_sitegroup()
    {
        midcom_show_style("/plugins/koe");
        
        ?>
        <form method="post" name="tkk_sitewizard_sitegroup">
        sitegroup<input type="text" name="tkk_sitewizard_hostname"/><br/>
        useri<input type="text" name="tkk_sitewizard_adminuser"/><br/>
        passi<input type="password" name="tkk_sitewizard_adminpass"/><br/>
        <input type="submit"/>
        </form>
            
        <?php
    }    
}

?>

