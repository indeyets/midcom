<?php
require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';


$GLOBALS['config'] = array (
          'username' => 'admin',
          'password' => 'password',
);


mgd_config_init('midgard.conf');
if (!mgd_auth_midgard($GLOBALS['config']["username"], $GLOBALS['config']["password"],false)) {
    die("Could not log in!");   
}

error_reporting(E_ALL);


require '../creation/base.php';
require '../creation/sitegroup.php';
require '../creation/config/config.php';


class midgard.admin.sitegroup_test_sitegroup_creation extends UnitTestCase {

    
    function test_validation_fails_with_no_values_set() 
    {
        
        $config = new midgard.admin.sitegroup_creation_config_sitegroup();
        
        $runner = new midgard.admin.sitegroup_creation_sitegroup($config);
        
        $this->assertFalse($runner->validate());
        
    }

    function test_validation_fails_when_no_auth_is_set() {
           
        $config = new midgard.admin.sitegroup_creation_config_sitegroup();
        $config->set_value("admin_password", 'pwd');
        $runner = new midgard.admin.sitegroup_creation_sitegroup($config);

        $this->assertFalse($runner->validate());
        
        $config->set_value("sitegroup_name", 'test4');
        $runner = new midgard.admin.sitegroup_creation_sitegroup($config);
        
        $this->assertTrue($runner->validate());
        
        $this->assertTrue(mgd_unsetuid());
        $this->assertFalse($runner->validate());
        
         
    }
    
    
    function test_creating_a_sitegroup() {
        $config = new midgard.admin.sitegroup_creation_config_sitegroup();
        $config->set_value("admin_password", 'pwd');
        $config->set_value("admingroup_name", 'pwd' . rand());
        $runner = new midgard.admin.sitegroup_creation_sitegroup($config);

        $this->assertFalse($runner->validate());
        
        $config->set_value("sitegroup_name", 'test_' . rand());
        $runner = new midgard.admin.sitegroup_creation_sitegroup($config);
        if (!mgd_auth_midgard($GLOBALS['config']["username"], $GLOBALS['config']["password"],false)) 
        {
            die("Could not log in!");   
        }
    
        if (!$this->assertTrue($runner->validate(), "Runner doens't validate! " )) {
            return false;
        }    
        
        $this->assertTrue($runner->run());
        
    }

    
    
}


if (realpath($_SERVER['PHP_SELF']) == __FILE__)
{

    $test = new midgard.admin.sitegroup_test_sitegroup_creation();
    $test->run (new TextReporter());

}
