<?php

/**
 * This is a plugin for selecting a structure
 */
class create_tkk_website extends midcom_baseclasses_components_handler
{
   /**
    * Simple constructor, which only initializes the parent constructor.
    */
    function create_tkk_website()
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
	            'handler' => array('create_tkk_website', 'create_website'),
	        ),
	    );
    }
    
    function _handler_create_website()
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
    
        $title = $this->_l10n->get('website creation');
        $_MIDCOM->set_pagetitle($title);
        
        
        if (   isset($_POST['tkk_sitewizard_website_submit'])   
            && !empty($_POST['tkk_sitewizard_website_submit']))
        {    
            $session = new midcom_service_session();
            
            if (!$session->exists("midgard_admin_wizards_koe"))
            {
                echo "HERE";
            }
            else
            {
                $structure_creator = $session->get("midgard_admin_wizards_koe");
            }
            
            try
            {
                $structure_creator->set_verbose(true);
                $structure_creator->execute(); 

                $this->_request_data['report'] = $structure_creator;
                
                //$_MIDCOM->relocate($prefix);
            }
            catch (midgard_admin_sitewizard_exception $e)
            {
                $e->error();
                echo "WE SHOULD HANDLE THIS \n";
            }
        }
        
        return true;
    }
    
    function _show_create_website()
    {     
        ?>   
        <fieldset>
            <?php
            if (isset($this->_request_data['report']))
            {
                echo "<pre>";   
                print_r($this->_request_data['report']);
                echo "</pre>";
            }
            ?>
        </fieldset>

        <form method="post" name="tkk_sitewizard_website">
        
        <input type="submit" name="tkk_sitewizard_website_submit" value="<?php echo $this->_l10n->get('create'); ?>">
        </form>    
        <?php
    }    
}

?>

