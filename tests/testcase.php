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

class midcom_tests_testcase extends PHPUnit_Framework_TestCase
{    
    protected function setUp()
    {
        if (MIDCOM_TESTS_ENABLE_OUTPUT)
        {
            echo "\nsetUp\n\n";
        }
        
        if (   !extension_loaded('midgard')
            && !extension_loaded('midgard2'))
        {
            $this->markTestSkipped('Midgard extension is not available');
        }
        
        if (! isset($_MIDGARD))
        {
            // Start up a Midgard connection
            $cnc = new midgard_connection();
            $cnc->open(MIDGARD_CONFIG);
            $cnc->set_debuglevel(MIDCOM_TESTS_LOGLEVEL);
            
            if (is_int(MIDCOM_TESTS_SITEGROUP))
            {
                $sg = mgd_get_sitegroup(MIDCOM_TESTS_SITEGROUP);
                $cnc->set_sitegroup($sg->name);
            }
            else
            {
                $cnc->set_sitegroup(MIDCOM_TESTS_SITEGROUP);
            }
        }        
        
        if (! isset($_MIDCOM))
        {
            // Load MidCOM with the manual dispatcher
            $_MIDCOM = new midcom_core_midcom('manual');            
        }
    }
    
    protected function tearDown()
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
        $_MIDCOM->context->delete();
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