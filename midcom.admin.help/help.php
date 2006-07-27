<?php
/**
 * Basic handler class
 * @package aegir.admin.help
 */

class midcom_admin_help_help extends midcom_baseclasses_components_handler {

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
    

    function midcom_admin_help_help() 
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    function _on_initialize() 
    {
        // Populate the request data with references to the class members we might need
        if (array_key_exists('aegir_interface',$this->_request_data)) 
        {
           $this->_config =& $this->_request_data['aegir_interface']->get_handler_config('midcom.admin.help');
        }
        // doing this here as this component most probably will not be called by itself.
        $_MIDCOM->style->prepend_component_styledir('midcom.admin.help');
        
        $_MIDCOM->load_library('net.nehmer.markdown');
          
        
    }
    /**
     * Load the file from the domponents documentation directory.
     */
    function _load_file ($file) 
    {
        if (array_key_exists('aegir_interface',$this->_request_data)) {
           $component = & $this->_request_data['aegir_interface']->_module;
        } 
        else 
        {
           $component = $this->_master->component;
        }
        // todo make it support multiple languages!
        $file = MIDCOM_ROOT . str_replace('.','/', $component) . "/documentation/" . $help_title . ".en.txt";
        
        if ( !file_exists( $file ) )
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Cannot generate help from file: " . $file );
            // this will exit
        }
        
        return file_get_contents( $file);
    }
    
    function _handler_show ($handler_id, $args, &$data)
    {
        $help_title = $args[0];
        
        $marker = new net_nehmer_markdown_markdown;
        $text = $this->_load_file($args[0]);
        $this->_request_data['html'] = $marker->render($text);
        return true;
        
    }
    
    function _show_show() {
        midcom_show_style('show_help'); 
    }
    
    
    function _handler_edit ($handler_id, $args, &$data)
    {
        // not yet implemented
        return false;
    }
    
    function _show_edit() 
    {
        
        //$this->_controller->display_form();
    }
    
    
    function _prepare_main_toolbar() 
    {
        if (array_key_exists('aegir_interface', $this->_request_data)) 
        {
            $this->_request_data['aegir_interface']->prepare_toolbar();
            $this->_request_data['aegir_interface']->set_current_node($this->_object->guid);
            $this->_request_data['aegir_interface']->generate_location_bar();
        }
        return;
    }
    
}

?>