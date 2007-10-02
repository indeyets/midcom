<?php

/**
 * This is a plugin for creatin a sitegroup
 */
class create_tkk_host extends midcom_baseclasses_components_handler
{
    var $_host_guid = '';
    var $_structure_config_path = '';
    var $_verbose = false;
    var $_home_name = '';
    var $_home_title = '';
    var $_creation_root_topic_guid = '';
    var $_creation_root_topic_parent_guid = '';
    var $_creation_root_topic_component = '';
    var $_creation_root_topic_parameters = array();
    var $_creation_root_group_guid = '';
    var $_creation_root_group_parent_guid ='';
    var $_creation_root_group_name = '';
    
    var $_logger = null;
    var $_team_guid = '';

   /**
    * Simple constructor, which only initializes the parent constructor.
    */
    function create_tkk_host()
    {
	    parent::midcom_baseclasses_components_handler();
    }

    function _on_initialize()
    {
        require_once($this->_request_data['plugin_config']['sitewizard_path']);

        parent::_on_initialize();
        
      }

    function get_plugin_handlers()
    {
        return array
        (
	        'sitewizard' => array
	        (
	            'handler' => array('create_tkk_host', 'create_sitegroup'),
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
                
                $_MIDCOM->relocate($prefix);
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
        <form method="post" name="tkk_sitewizard_host">
        hosti<input type="text" name="tkk_sitewizard_hostname"/><br/>
        useri<input type="text" name="tkk_sitewizard_adminuser"/><br/>
        passi<input type="password" name="tkk_sitewizard_adminpass"/><br/>
        <input type="submit"/>
        </form>
            
        <?php
    }    
}

?>

