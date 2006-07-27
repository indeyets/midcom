<?php
/**
 * @package no.bergfald.objectbrowser
 */
require_once '../../../../midcom/admin/aegir/tests/config.php';
require_once( 'navigation.php');
$config = new AegirTestConfig(); 

class groupTestFactory extends GroupTest{

    function groupTestFactory ($config ) {
        $this->GroupTest('ObjectBrowser tests');
        $this->AddTestCase(new ObjectBrowserNavigationTest());
    }
}

$tests = new groupTestFactory($config);
$tests->run (new TextReporter());
