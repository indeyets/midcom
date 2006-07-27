<?php

$_MIDCOM->componentloader->load('midcom.admin.aegir');
/**
 * the Aegir handler for this module.
 * @package midcom.admin.styleeditor
 * Also this module contains the request_switch for now.
 */ 

class midcom_admin_styleeditor_aegir extends midcom_admin_aegir_module {

    /**
     * Maximum level for the menu to decend and expand nodes.
     * Styles often hav every broad trees, therefore we keep them unexpanded. 
     * @var int number of levels
     */
    var $nav_maxlevel = 1;
    function midcom_admin_styleeditor_aegir ()
    { 
        parent::midcom_admin_aegir_interface();
    }
    /*
     * function to get the request array.
     * 
     * The urls will arive in the following manner:
     * edit/<page_guid>
     * edit/<page_guid>/<element_name>
     * configure/<page_guid> 
     * new/<page_guid>
     * */
    function get_request_switch() {
    
        
        $request_switch['edit_element'] = Array
        (
            'fixed_args' => array('styleeditor','element'),
            'handler' => array('midcom_admin_styleeditor_handler_element','edit'),
            'variable_args' => 1,
        );
        $request_switch[] = Array
        (
            'fixed_args' => array('styleeditor','element','edit'),
            'handler' => array('midcom_admin_styleeditor_handler_element','edit'),
            'variable_args' => 2,
        );
        $request_switch['element_delete'] = Array
        (
            'fixed_args' => array('styleeditor','element', 'delete'),
            'handler' => array('midcom_admin_styleeditor_handler_element','delete'),
            'variable_args' => 1,
        );
        
        $request_switch['element_new'] = Array
        (
            'fixed_args' => array('styleeditor','element','new'),
            'handler' => array('midcom_admin_styleeditor_handler_element','create'),
            'variable_args' => 1,
        );
        /* this one must be over the create handler. */
        $request_switch['create_root'] = Array
        (
            'fixed_args' => array('styleeditor', 'style', 'create', 'root'),
            'handler' => array('midcom_admin_styleeditor_handler_style','create_root'),
            'variable_args' => 0// parent style guid and name.
        );
        $request_switch['create'] = Array
        (
            'fixed_args' => array('styleeditor', 'style', 'create'),
            'handler' => array('midcom_admin_styleeditor_handler_style','create'),
            'variable_args' => 1 // parent style guid and name.
        );
        
        $request_switch['delete'] = Array
        (
            'fixed_args' => array('styleeditor', 'style','delete'),
            'handler' => array('midcom_admin_styleeditor_handler_style','delete'),
            'variable_args' => 1, // style guid and name.
        );
        
        $request_switch['edit_style'] = Array
        (
            'fixed_args' => array('styleeditor', 'style'),
            'handler' => array('midcom_admin_styleeditor_handler_style','edit'),
            'variable_args' => 2, // style guid and name.
        );
        
        $request_switch[] = Array
        (
            'fixed_args' => array('styleeditor', 'style'),
            'handler' => array('midcom_admin_styleeditor_handler_style','edit'),
            'variable_args' => 1, // style guid and name.
        );
        
        $request_switch['index'] = Array
        (
            'fixed_args' => array('styleeditor'),
            'handler' => array('midcom_admin_styleeditor_handler_style','index'),
            'variable_args' => 0 // style guid and name.
        );
        
        $request_switch[] = Array
        (
            'fixed_args' => array('styleeditor','configure'),
            'handler' => array('midcom_admin_styleeditor_handler_style','configure'),
            'variable_args' => 1,
        );
        
                
        return $request_switch;
    
    }
    /**
     * Use the visitor pattern to update the locations in the toolbar both for 
     * styles and style elements.
     */
    function update_toolbar(&$object) 
    {
        $this->get_navigation();
        if ($object->_current !== null) {
            if (is_a ($object->_current, 'midcom_db_style' )) {
                $this->_nav->set_current_node($object->_current->guid);    
            } else {
                $this->_nav->set_current_node($object->_style->guid);
                $this->_nav->set_current_leaf($object->_current->guid);
            }
        }
        
        $this->generate_location_bar ();
            
    }
    
}
?>
