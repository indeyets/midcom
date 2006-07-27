<?php

 $curr_path = dirname(__FILE__);
require_once $curr_path . '/../../aegir/tests/navigation.php';
require_once $curr_path . '/../aegir_navigation.php';

$GLOBALS['testclasses'] = array ('midcom_admin_simple_content_navigation_test' => 1); 
$GLOBALS['testconfig'] = 'midcom_admin_aegir_tests_config'; 

class midcom_admin_simple_content_navigation_test extends midcom_admin_aegir_tests_navigation {


    var $nodes = array (16,19);
    var $non_nodes = array ("rr");
    var $leaves = array(6,8);
    var $verbose = false;

    /*   */

    function setUp () {
        $this->nav = new midcom_admin_simplecontent_aegir_navigation();
    }

} 
