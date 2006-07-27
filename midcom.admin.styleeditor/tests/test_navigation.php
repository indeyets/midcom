<?php
/* get the correct include: */
$curr_path = dirname(__FILE__);

require_once $curr_path . '/../../../../midcom/admin/aegir/tests/navigation.php';
require_once $curr_path . '/../aegir_navigation.php';


$GLOBALS['testclasses'] = array ('midcom_admin_styleeditor_tests_navigation' => 1);

class midcom_admin_styleeditor_tests_navigation extends midcom_admin_aegir_tests_navigation {


    var $nodes = array (16,19);
    var $non_nodes = array ("rr");
    var $leaves = array(6,8);
    var $verbose = true;

    /*   */

    function setUp () {
        $this->nav = new midcom_admin_styleeditor_aegir_navigation();
    }

} 
