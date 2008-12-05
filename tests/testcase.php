<?php
/**
 * @package midcom_tests
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

if (! defined('MIDCOM_TEST_RUN'))
{
    define('MIDCOM_TEST_RUN', true);
}

if (! defined('MIDGARD_CONFIG')) {
    define('MIDGARD_CONFIG', 'midgard');
}

if (! defined('MIDCOM_TESTS_LOGLEVEL'))
{
    define('MIDCOM_TESTS_LOGLEVEL', 'info');
}
if (! defined('MIDCOM_TESTS_SITEGROUP'))
{
    define('MIDCOM_TESTS_SITEGROUP', 1);
}
if (! defined('MIDCOM_TESTS_ENABLE_OUTPUT')) {
    define('MIDCOM_TESTS_ENABLE_OUTPUT', false);
}

if (! defined('COMPONENT_DIR')) {
    define('COMPONENT_DIR', realpath(dirname(__FILE__) . '/../'));
}

require_once('PHPUnit/Framework.php');

require_once('midcom_core/framework.php');

//TODO: fix to correct path after packaged
require_once('midgard_test.php');

/**
 * @package midcom_tests
 */
class midcom_tests_testcase extends midgard_test
{
    protected static $database_created = false;
    
    public function setUp()
    {
        if (MIDCOM_TESTS_ENABLE_OUTPUT)
        {
            echo "\nsetUp\n\n";
        }
        
        $db_name = $db_user = $db_pass = 'midgard_php_test';
        
        if (! $this->create_database_with_privileges($db_name, $db_user, $db_pass, 'root', 'root'))
        {
            $this->markTestSkipped("Failed to create database '{$db_name}' for tests");
        }
        
        parent::setUp();
        
        if (! isset($_MIDCOM))
        {
            // Load MidCOM with the manual dispatcher
            $_MIDCOM = new midcom_core_midcom('manual');            
        }
    }
    
    private function create_database_with_privileges($db_name, $db_user, $db_pass, $mysql_user='root', $mysql_pass=null)
    {
        if (midcom_tests_testcase::$database_created) {
            return true;
        }
        
        $create_sql = "DROP DATABASE IF EXISTS {$db_name}; CREATE DATABASE {$db_name} CHARACTER SET utf8;";
        $privilege_sql = "GRANT all ON {$db_name}.*  TO '{$db_user}'@'localhost' IDENTIFIED BY '{$db_pass}'; FLUSH PRIVILEGES;";
        
        if (! is_null($mysql_pass))
        {
            $mysql_pass = "-p{$mysql_pass}";
        }
        
        $cmd = "mysql -u {$mysql_user} {$mysql_pass} -e \"{$create_sql}\"";
        exec($cmd, $ouput, $ret);

        if (   $ret !== 0
            && $ret !== 1)
        {
            $this->markTestSkipped('Failed to create database');
            return false;
        }
        
        $cmd = "mysql -u {$mysql_user} {$mysql_pass} -e \"{$privilege_sql}\"";
        exec($cmd, $ouput, $ret);

        if (   $ret !== 0
            && $ret !== 1)
        {
            $this->markTestSkipped('Failed to assign privileges');
            return false;
        }
        
        midcom_tests_testcase::$database_created = true;
        
        return true;
    }
    
    public function tearDown()
    {
        if (MIDCOM_TESTS_ENABLE_OUTPUT)
        {
            echo "\n\ntearDown\n\n";
        }
        
        if (   $_MIDCOM->timer
            && MIDCOM_TESTS_ENABLE_OUTPUT)
        {
            $_MIDCOM->timer->display();
        }
        
        // Delete the context        
        //$_MIDCOM->context->delete();
        
        parent::tearDown();
    }
    
    protected function create_context($component_name=null)
    {
        if (is_null($component_name))
        {
            return false;
        }
        
        $manifest = $_MIDCOM->componentloader->manifests[$component_name];
        
        // Enter new context
        $_MIDCOM->context->create();
        try
        {
            $_MIDCOM->dispatcher->initialize($component_name);
        }
        catch (Exception $e)
        {
            if (MIDCOM_TESTS_ENABLE_OUTPUT)
            {
                echo "Failed to load {$component_name}\n";
            }
            $_MIDCOM->context->delete();
            return false;
        }

        if (! $_MIDCOM->context->component_instance)
        {
            if (MIDCOM_TESTS_ENABLE_OUTPUT)
            {
                echo "Failed to load {$component_name}\n";
            }
            $_MIDCOM->context->delete();
            return false;
        }
    }
}
?>