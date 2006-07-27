<?php
/**
 * Aegir handler class.
 * @package no.bergfald.objectbrowser
 */
$_MIDCOM->componentloader->load('midcom.admin.aegir');

class no_bergfald_rcs_aegir extends midcom_admin_aegir_module {


    function no_bergfald_rcs_aegir ()
    { 
        parent::midcom_admin_aegir_interface();
    }
    
    /**
     * Static function, returns the request array for the rcs functions.
     * Add this to your _on_initialize function in the calling request:
     * <pre>
     * $rcs_array =  no_bergfald_rcs::get_request_array();
     * $this->request_switch = array_merge($this->request_switch, $rcs_array)
     * </pre>
     * 
     * @param none
     * @returns array of request params 
     * 
     */
    function get_request_switch() 
    {
        $request_switch = array();
        
        $request_switch[] =  Array
        (
            'fixed_args' => 'rcs',
            'handler' => array('no_bergfald_rcs_handler','history'),
            'variable_args' => 2,
        );
        
        $request_switch[] =  Array
        (
            'fixed_args' => array('rcs','preview'),
            'handler' => array('no_bergfald_rcs_handler','preview'),
            'variable_args' => 3,
        );
        $request_switch[] =  Array
        (
            'fixed_args' => array('rcs', 'diff'),
            'handler' => array('no_bergfald_rcs_handler','diff'),
            'variable_args' => 4,
        );
        $request_switch[] =  Array
        (
            'fixed_args' => array('rcs', 'restore'),
            'handler' => array('no_bergfald_rcs_handler','restore'),
            'variable_args' => 3,
        );
        return $request_switch;
        
    }    
    
}
?>
