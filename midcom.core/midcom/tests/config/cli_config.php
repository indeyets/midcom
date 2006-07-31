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
// setup variables for unittests 

$base = dirname(__FILE__);

require_once($base . '/../base.php');


error_reporting(E_ALL);


/**
 * You'll need to modify the midgard.conf configuration file to run cli tests.
 */
mgd_config_init('midgard.conf');
mgd_auth_midgard($config->get_username(), $config->get_password(),false);


$config = new midcom_tests_config_config();
/**
 * Export the config so we're sure we got it later.
 * TODO: use static class instead.
 */
$GLOBALS['config'] = &$config;

