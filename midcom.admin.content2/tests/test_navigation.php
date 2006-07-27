<?php
/**
 * @package midcom.admin.content2
 */
 $curr_path = dirname(__FILE__);
require_once $curr_path . '/../../aegir/tests/navigation.php';
require_once $curr_path . '/../aegir_navigation.php';
/**
 * Make sure the globals statements are bellow the requires, as they contain them too!
 */
$GLOBALS['testclasses'] = array ('midcom_admin_content2_tests_navigation' => 1); 
$GLOBALS['testconfig'] = 'midcom_admin_aegir_tests_config'; 

require_once MIDCOM_ROOT . "/midcom/admin/content2/context.php";
class midcom_admin_content2_tests_navigation extends midcom_admin_aegir_tests_navigation {


    var $nodes = array ();
    var $non_nodes = array ("rr");
    var $leaves = array();
    var $verbose = true;

    /*   */

    function setUp () {
        $this->nav = new midcom_admin_content2_aegir_navigation();
    }

} 
