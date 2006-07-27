<?php
/**
 * @package midcom.admin.aegir
 * run the logintest. 
 */
 // this makes it work with the midcom.tests gui.
 
$curr_path = dirname(__FILE__);
require_once( 'config.php');
require_once('login.php');


$GLOBALS['testclasses'] =  array('aegir_login_test' => 1);
$GLOBALS['testconfig'] = 'midcom_admin_aegir_tests_config';

 
/** with this check the file may be included in other tests w/o problems */
if (realpath($_SERVER['PHP_SELF']) == __FILE__) {
    $test = new loginTest(&$config);
    $test->run(new TextReporter);
}

