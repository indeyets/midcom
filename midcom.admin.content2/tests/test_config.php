<?php
require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

//require_once '../../.././tests/config/cli_config.php';
/* get the correct include: */
$curr_path = dirname(__FILE__);
require_once $curr_path . '/../../aegir/tests/config.php';


/**
 * @package midcom.admin.content2
 * settings for midcom.tests
 */
$GLOBALS['testclasses'] = array ('midcom_admin_content2_tests_config' => 1); 
$GLOBALS['testconfig'] = 'midcom_admin_aegir_tests_config'; 
 
//require_once '../../../tests/config/cli_config.php';

function microtime_float()
    {
       list($usec, $sec) = explode(" ", microtime());
       return ((float)$usec + (float)$sec);
    }

class midcom_admin_content2_tests_config extends UnitTestCase {

    var $config = null;
    
    function midcom_admin_content2_tests_config($label = false, $config) {
        parent::UnitTestCase($label);
        $this->config = $config;
    }   



}

/** with this check the file may be included in other tests w/o problems */
if (realpath($_SERVER['PHP_SELF']) == __FILE__) {
    $test = new midcom.admin.content2_tests_config(&$config);
    $test->run(new TextReporter);
}


