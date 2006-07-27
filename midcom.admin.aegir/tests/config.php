<?php

/* basic requires needed by everyone  */
require_once 'simpletest/unit_tester.php';
require_once 'simpletest/web_tester.php';
require_once 'simpletest/reporter.php';
// framework classes


error_reporting(E_ALL);


class midcom_admin_aegir_tests_config extends midcom_tests_config {
    
    var $a_prefix = '/aegir';
    var $aegir_config = array();
    var $registry = array();
    
    
    function midcom_admin_aegir_tests_config($config ) {
        
        parent::midcom_tests_config($config);
        
        $path = dirname(__FILE__) . '/../config/config.inc';
        eval ( 
            '$this->aegir_config= array (' . file_get_contents( $path) .');'
             );
             
        $this->registry = $this->aegir_config['registry'];
    }
    
    function get_login_url() {
        return $this->get_base_url() . '/login';
    }    
}
