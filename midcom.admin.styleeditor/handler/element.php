<?php
/**
 * Created on Aug 3, 2005
 *
 * Create, edit and delete styleelements
 * 
 * The hard part is setting the context, i.e. which midcom, topic and host is involved.
 * urls:
 * edit element:  /edit/<elementguid>/<hostguid>/<topicguid>/element_name
 * edit element:  /edit/<elementguid>/<hostguid>/element_name
 * edit element:  /editmidcom/<elementguid>/<curr-midcom>/element_name
 * create:       /create/same as above.
 * delete         /delete/same as above.
 * 
 * /$id = view style $id
 * /edit/$id edit style $id
 * /create/$topic/$schema create new style in topiv $topic
 * /delete/$id delete style with id.
 * 
 * $this->_topic is set from the current style or from argv[0] in the case of create. 
 * @package midcom.admin.styleeditor
 */
 
require 'base.php';

class midcom_admin_styleeditor_handler_element extends midcom_admin_styleeditor_handler_base  {
    
   /**
    * The object we are currently editing
    * @var midcom_db_element or midcom_db_pageelement.
    */
   var $_current;
    
    /**
     * The module config.
     */
    var $_config = array();
    
    /**
     * The datamanager controller
     * @access private
     * @var midcom_helper_datamanager2_controller
     * 
     */
    var $_controller = null;
    /**
     * The current element, either a pageelement or a styleelemnt.
     * @var midcom_db_element or midcom_db_pageelement.
     */
    var $_current_element = null;
    
    /**
     * The schema the datamanager uses. It is either set by default or updatet by
     * the _set_schema function that is subclassed.
     */    
    var $_current_schema = 'file://midcom/admin/styleeditor/config/schemadb_element.inc';

    /**
     * The new element (used by creationmode)
     * @access private
     */
    var $_entry = null;        
    /**
     * Constructor
     */    
	function midcom_admin_styleeditor_handler_element() 
    {
	         parent::midcom_baseclasses_components_handler();
	}
	
	function _on_initialize() 
    {
		// Populate the request data with references to the class members we might need
        $this->_request_data['datamanager'] = & $this->_datamanager;
        $this->_request_data['toolbars']    = & midcom_helper_toolbars::get_instance();        
           
	}
    
    
    /**
     * This function only supports styleelements for now.
     */
    function _set_current_object( $args) {
        $this->_current = new midcom_db_element($args[0]);
        
        $this->_style   = new midcom_db_style ($this->_current->style); 
        if (!$this->_current) {
                        $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Cannot load the correct style element, aborting: Could not load the style {$objectguid} from the database (" 
                . mgd_errstr() . ').');
        }
        $this->_request_data['aegir_interface']->update_toolbar(&$this);
        $this->_request_data['object'] = &$this->_current;
    }
    

    function _generate_toolbar()
    {
        parent::_generate_toolbar();
        $current_guid = $this->_current->guid;
        $style_guid = $this->_style->guid;
        $toolbar = &midcom_helper_toolbars::get_instance();
      
        $toolbar->top->add_item(Array(
        MIDCOM_TOOLBAR_URL => $this->_master->get_prefix(). "element/delete/{$current_guid}.html",
        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("delete element"),
        MIDCOM_TOOLBAR_HELPTEXT => null,
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
        MIDCOM_TOOLBAR_ENABLED => true,
        MIDCOM_TOOLBAR_OPTIONS => array('accesskey' => 'e'),
        MIDCOM_TOOLBAR_HIDDEN =>
        ! (
              
           $_MIDCOM->auth->can_do('midgard:delete', $this->_current)
          )
        ));
    
    }
        

    function _handle_view ( $handler_id, $args, &$data) 
    {
        $this->_set_current_object($args[0]);
        return true;
    }
    
    
    /**
     * show an element
     */
    function _show_view() 
    {
        midcom_show_style("show_element");
    }
    
    /**
     * Simple function to get the up object
     * Used by handler_delete
     * @return midcom_db_style
     */
    function get_up() 
    {
        return new midcom_db_style($this->_current->style);
    }    
    
    /**
     * Get info about the object. Used by the delete handler
     * Sets the name and content keys of _request_data.
     */
    
    function _get_object_info()
    {
        $this->_request_data['name'] = $this->_current->name;
        $this->_request_data['content'] = $this->_current->value;
    } 
    
    /**
     * TODO: Rewrite this so it handles creation elements based on some other
     * style or midcom.
     */
    
    function _handler_create($handler_id, $args, & $data)
    {
        $this->_current = new midcom_db_style($args[0]);
        $this->_style   = $this->_current;
        $this->_request_data['aegir_interface']->update_toolbar(&$this);
        //$defaults = $this->get_create_defaults();
        $defaults = array();        
        $result = $this->_run_datamanager('create', $defaults);
        switch ($result) {
            case "save":
                $_MIDCOM->relocate("styleeditor/element/" . $this->_entry->guid);
                // this will exit
            break;
            
            case "cancel":
                $_MIDCOM->relocate("styleeditor/style/" . $this->_style->guid);
                // this will exit
            break;
        }
                
        $this->_generate_toolbar();
        return true;
    }
    
    function _show_create()
    {
        
        midcom_show_style("admin_edit2");
    }
    
    function & dm2_create_callback (&$controller ) 
    {
        $this->_entry = new midcom_db_element();
        $this->_entry->name = "_tmp_";
        $this->_entry->style = $this->_style->id;
        if (! $this->_entry->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_entry);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new element, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }
        $id = $this->_entry->id;
        $this->_entry = new midcom_db_element($id);
        return $this->_entry;
       
    }
    
    function get_creation_relocate ($guid) 
    {
        return "styleeditor/element/$guid";
    }
        
}