<?php
/**
 * Created on Aug 3, 2005
 *
 * Create, edit and delete basic objects
 * 
 * This class abstracts out the main parts of the create - edit delete cycle for styles and 
 * styleelements.
 * 
 * $this->_topic is set from the current style or from argv[0] in the case of create. 
 * @package midcom.admin.styleeditor
 */
 
require 'base.php';

class midcom_admin_styleeditor_handler_style extends midcom_admin_styleeditor_handler_base {
     
    /**
     * The schema the datamanager uses. It is either set by default or updatet by
     * the _set_schema function that is subclassed.
     */    
    var $_current_schema = 'file://midcom/admin/styleeditor/config/schemadb_style.inc';
        
    /**
     * Constructor
     */    
	function midcom_admin_styleeditor_handler_style() 
    {
	         parent::midcom_admin_styleeditor_handler_base();
	}
	
	function _on_initialize() 
    {
        
        parent::_on_initialize();
        
	}
    
    function _handler_index() {
        $toolbar = &midcom_helper_toolbars::get_instance();
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "styleeditor/style/create/root.html",
            MIDCOM_TOOLBAR_LABEL => $this->_request_data["l10n"]->get("new root style"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        return true;
    }

    function _show_index() {
        
    }
    
    /**
     * This function should be subclassed out into the subclasses
     * Abstract function. 
     */
    function _set_current_object( $args ) 
    {
        $this->_current = new midcom_db_style($args[0]);
        $this->_style = &$this->_current;
        if (!$this->_current) 
        {
            //$obj = mgd_get_object_by_guid($args[0]);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "could not load style with id " . $args[0] 
                );
            // this will exit.
        }
        $this->_set_style = $this->_current->id;
        $this->_request_data['aegir_interface']->update_toolbar(&$this);
        $this->_request_data['object'] = &$this->_current;
    }
    /**
     * Only style creates a root so this is just used in style.
     */
    function _handler_create_root($handler_id, $args, &$data) 
    {
        $this->_set_schema($handler_id);
        /* no ddefaults as that would have been the up id. */
        $defaults = array('up' => 0);
        $result = $this->_run_datamanager( 'nullstorage', $defaults);
        
        if ($result == 'save' ) 
        {
            $object =& $this->dm2_create_callback();
            if ($object) 
            {
                $this->_current =&$object;
                $result = $this->_run_datamanager('simple');
                if ($result == 'edit') {
                    $_MIDCOM->generate_error("Could not update created object with id $id!");
                    // this will exit.
                }
                $_MIDCOM->relocate($this->get_creation_relocate($object->guid));
            } 
            else 
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not create object.");
            }
        }
        
        //$this->_generate_toolbar();
        return true;
    }
    /**
     * Sets the schema.
     * @access protected
     * @var string  handler_id
     */
    function _set_schema($handler_id) 
    {
        switch ($handler_id) 
        {
            case 'create_root':            
            case 'create':
                $this->_current_schema = 'file://midcom/admin/styleeditor/config/schemadb_style_creation.inc';
                break;
            default:
            break;
        }
    }
    /**
     * Function used by handler_create to set simple texts etc.
     * @params none
     * @return void.
     */
    function _on_create() 
    {
        $this->_request_data['title'] = $this->_l10n->get('Create new style');
    }
    /**
     * Show the creation dm form. Calls _create_root.
     * 
     */
    function _show_create_root () {
        $this->_request_data['title'] = $this->_l10n->get('Create new root style');
    
        $this->_show_create();
    }
    
    /**
     * Simple callback to generate defaults for the object 
     */
    function get_create_defaults() {

        return array ('up' => $this->_current->id);   
    }
    /**
     * Simple callback to create the object
     */
    function & dm2_create_callback ( &$controller) 
    {
        $object =  new midcom_db_style;
        $id = $object->create();
        if ($id) 
        {
            //return new midcom_db_style($object->);
            return $object;
        }
        $id = false;
        return $id; 
    }
    
    /**
     * Another abstract function. This generates the toolbar on top.
     */
    function _generate_toolbar() {
        parent::_generate_toolbar();
        $toolbar = &midcom_helper_toolbars::get_instance();
        
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "styleeditor/style/edit/{$this->_current->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_request_data["l10n"]->get("edit"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit-folder.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => (($_MIDCOM->auth->can_do('midgard:update', $this->_current->guid) == false))
        )); 
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "styleeditor/style/delete/{$this->_current->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_request_data["l10n"]->get("delete"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => (($_MIDCOM->auth->can_do('midgard:update', $this->_current->guid) == false))
        ));
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "styleeditor/style/move/{$this->_current->guid}/{$this->_current->name}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_request_data["l10n_midcom"]->get('move'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/topic-score.png',
            MIDCOM_TOOLBAR_ENABLED => false,
            MIDCOM_TOOLBAR_HIDDEN => (($_MIDCOM->auth->can_do('midgard:update', $this->_current->guid) == false))
        ));
        /* todo make attachmentshandler... */
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "styleeditor/style/attachments/{$this->_current->guid}/{$this->_current->name}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_request_data["l10n"]->get("style attachments"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/attach.png',
            MIDCOM_TOOLBAR_ENABLED => false,
            MIDCOM_TOOLBAR_HIDDEN => (($_MIDCOM->auth->can_do('midgard:update', $this->_current->guid) == false))
        ));
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "rcs/{$this->_current->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_request_data["l10n"]->get("style changes"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/attach.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
    }
        
    
    /**
     * Get info about the object. Used by the delete handler
     * Sets the name and content keys of _request_data.
     */
    function _get_object_info()
    {
        $this->_request_data['name'] = $this->_current->name;
        $this->_request_data['content'] = '';
    } 
    
    /**
     * Simple function to get the up object
     * @return midcom_db_style
     */
    function get_up() 
    {
        return ($this->_current->up == 0 ) ? 0 : new midcom_db_style($this->_current->up);
    }    
        
}
