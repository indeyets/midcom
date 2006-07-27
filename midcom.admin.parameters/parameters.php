<?php
/**
 * Basic handler class
 * @package aegir.admin.parameters
 */

class midcom_admin_parameters_parameters extends midcom_baseclasses_components_handler {

    /**
     * pointer to the object in question
     */
    var $_object = null;

    /**
     * The datamanager used to edit the component
     *
     * @var midcom_helper_datamanager2_controller
     * @access private
     */
    var $_controller = null;
    /**
     * Helper variable, containg a localized message to be shown to the user indicating the form's
     * processing state.
     *
     * @var string
     * @access private
     */
    var $_processing_msg = '';
    
    /**
     * Pointer to the module configuration 
     */
    var $_config = null;
    /**
     * The schema to use for the current dm
     */
    var $_schema;
    

    function midcom_admin_parameters_parameters() 
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    function _on_initialize() 
    {
        // Populate the request data with references to the class members we might need
        if (array_key_exists('aegir_interface',$this->_request_data)) {
           $this->_config = &$this->_request_data['aegir_interface']->get_handler_config('midcom.admin.parameters');
        }
        // doing this here as this component most probably will not be called by itself.
        $_MIDCOM->style->prepend_component_styledir('midcom.admin.parameters');
        /** @todo handle the else case */
        $this->_request_data['toolbars']    = & midcom_helper_toolbars::get_instance();
        $_MIDCOM->load_library('midcom.helper.datamanager2');
          
        
    }
    /**
     * generate the datamanager controller instance and run it.
     * Note: 
     * Nullstorage users have to run process_form themselves.
     * @param int id of topic 
     * @param string type of controller (simple or nullstorage)
     */ 
    function _run_datamanager($component_id, $type, $defaults = array ()) 
    {
        if (array_key_exists('aegir_interface', $this->_request_data))
        {
            // set the current node
            $this->_request_data['aegir_interface']->set_current_node($this->_object->id);
        }
        $this->_controller =& midcom_helper_datamanager2_controller::create($type);
        $this->_controller->set_schemadb(&$this->_schema);
        
        if ($type != 'nullstorage') 
        {
            $this->_controller->set_storage(&$this->_object,'parameters');
        }
        $this->_controller->defaults = $defaults;
        $this->_controller->initialize();
        $this->_controller->formmanager->create_renderer( 'simple');
        $this->_request_data['datamanager'] = & $this->_controller;
        
        return $this->_controller->process_form();
    }
    
    function _handler_edit ($handler_id, $args, &$data)
    {
         
        $this->_object = $_MIDCOM->dbfactory->get_object_by_guid($args[0]);
        
        $defaults = array();
        $this->_schema = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_parameters'));
        
        if ($this->_run_datamanager($args[0], 'simple', $defaults) == 'save' ) 
        {
            $_MIDCOM->relocate($_MIDGARD['uri']);
        }
        
        $this->_prepare_main_toolbar();
        
        return true;
    }
    
    function _show_edit() 
    {
        midcom_show_style('parameter-edit');
    }
    
    
    function _prepare_main_toolbar() 
    {
        if (array_key_exists('aegir_interface', $this->_request_data)) 
        {
            $this->_request_data['aegir_interface']->prepare_toolbar();
            $this->_request_data['aegir_interface']->set_current_node(&$this->_object);
            $this->_request_data['aegir_interface']->generate_location_bar();
        }
        return;
        
    }
    
}

?>