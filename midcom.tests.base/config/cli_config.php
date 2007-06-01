<?php
/**
 * @package midcom.tests
 * This file contains the setup I use when I run tests from the commandline
 * (where that is possible).
 */
 /* basic requires needed by everyone  */
require_once 'simpletest/unit_tester.php';
require_once 'simpletest/web_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';
// setup variables for unittests 

$base = dirname(__FILE__);

//require_once($base . '/../base.php');


/*
 * this file makes sure the _MIDCOM 
 */
$base = dirname( __FILE__) . '/';
require_once ( $base . "../lib/root.php" );
define ('MIDCOM_ROOT', realpath( $base.'../../'));

require_once MIDCOM_ROOT . '/../constants.php';
require_once MIDCOM_ROOT. '/debug.php';
require_once MIDCOM_ROOT. '/helper/misc.php';

require_once MIDCOM_ROOT . '/application.php';

$GLOBALS["midcom_debugger"]->setLogFile('/tmp/midcom_test.log');
$GLOBALS["midcom_debugger"]->setLogLevel(5);
$GLOBALS["midcom_debugger"]->enable();

error_reporting(E_ALL);


/**
 * You'll need to modify the midgard.conf configuration file to run cli tests.
 */
if (!isset ($_MIDGARD)) {
    mgd_config_init('midgard');
    
    mgd_auth_midgard('admin', 'password',false);
}

/**
 * Export the config so we're sure we got it later.
 * TODO: use static class instead.
 */
//$GLOBALS['config'] = &$config;

