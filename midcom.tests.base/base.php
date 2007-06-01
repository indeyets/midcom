<?php

/**
 * this file sets up a Midcom environment for testing. 
 * The file it not nearly done nor is it working too well, but it is
 * a start.
 * @package midcom.tests
 * */


/*
 * this file makes sure the _MIDCOM 
 */
$base = dirname( __FILE__) . '/';
define ('MIDCOM_ROOT', realpath( $base.'../../'));

require_once MIDCOM_ROOT . '/constants.php';
require_once MIDCOM_ROOT. '/midcom/debug.php';
require_once MIDCOM_ROOT. '/midcom/helper/misc.php';




/**
 * Midcom configuration
 */
require_once 'config.php';
$config = new midcom_tests_config_config;
$GLOBALS['midcom_config'] = $config->get_midcom_config();
/**
 * testcases
 * */
require_once 'lib/application.php';
/*
 * Classes to be mocked:
 */
require_once MIDCOM_ROOT . '/midcom/application.php';
//require_once MIDCOM_ROOT . '/midcom.php';
// needed stuff

if (!Mock::generate('midcom_application'))  {
    print "Mock failed!\n";
    exit();
}
$midcom_tests = new midcom_tests_lib_application;
$GLOBALS['midcom']= new Mockmidcom_application(&$midcom_tests);

