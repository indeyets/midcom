<?php
/**
 * @package midcom_tests
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

if (! defined('MIDCOM_TESTS_ENABLE_OUTPUT')) {
    define('MIDCOM_TESTS_ENABLE_OUTPUT', false);
}

if (! defined('COMPONENT_DIR')) {
    define('COMPONENT_DIR', realpath(dirname(__FILE__) . '/../'));
}

require_once('Testing/Selenium.php');
require_once('PHPUnit/Framework.php');

/**
 * @package midcom_tests
 */
class midcom_tests_seleniumcase extends PHPUnit_Framework_TestCase
{
    protected $selenium;
    
    protected function setUp()
    {
        if (MIDCOM_TESTS_ENABLE_OUTPUT)
        {
            echo "\nsetUp\n\n";
        }
    }
    
    protected function tearDown()
    {
        if (MIDCOM_TESTS_ENABLE_OUTPUT)
        {
            echo "\n\ntearDown\n\n";
        }

        try
        {
           $this->selenium->stop();
        } catch (Testing_Selenium_Exception $e) {
            $this->fail("Unexcepted exception thrown!\n{$e}\n");
        }
    }

}
?>