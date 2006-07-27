<?php

require_once '../../../../midcom/admin/aegir/tests/config.php';
require_once( 'navigation.php');
$config = new AegirTestConfig(); 

class groupTestFactory extends GroupTest{

    function groupTestFactory ($config ) {
        $this->GroupTest('Simple content tests');
        $this->AddTestCase(new SimpleContentNavigationTest());
    }
}

$tests = new groupTestFactory($config);
$tests->run (new TextReporter());
