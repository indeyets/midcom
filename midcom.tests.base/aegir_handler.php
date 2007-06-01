<?php

$_MIDCOM->componentloader->load('midcom.admin.aegir');
/**
 * the Aegir handler for this module.
 * @package midcom.tests
 * Also this module contains the request_switch for now.
 */ 

class midcom_tests_aegir extends midcom_admin_aegir_module {


    function midcom_tests_aegir ()
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
    
                $request_switch[] = Array
        (
            'fixed_args' => array('tests'),
            'handler' => array('midcom_tests','index'),
            'variable_args' => 0,
        );
        $request_switch[] = Array
        (
            'fixed_args' => array('tests'),
            'handler' => array('midcom_tests','run_test'),
            'variable_args' => 2,
        );               
        $request_switch[] = Array
        (
            'fixed_args' => array('tests'),
            'handler' => array('midcom_tests','show_tests_for_midcom'),
            'variable_args' => 1,
        );                
        return $request_switch;
    
    }
}
