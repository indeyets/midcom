<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

if (! defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'midcom_tests_selenium_all::main');
}
if (! defined('COMPONENT_DIR')) {
    define('COMPONENT_DIR', '/projects/midcom/midcom3_0/midcom');
}

if (! defined('MIDCOM_TESTS_ENABLE_OUTPUT')) {
    define('MIDCOM_TESTS_ENABLE_OUTPUT', true);
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once('tests/helpers.php');

class midcom_tests_selenium_all
{   
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
    
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('MidCOM selenium');
        
        $tests = midcom_core_tests_helpers::get_tests(__FILE__, __CLASS__, array('server', 'start_server'));
        foreach ($tests as $test)
        {
            $suite->addTestSuite($test);
        }
 
        return $suite;
    }
}
 
if (PHPUnit_MAIN_METHOD == 'midcom_tests_selenium_all::main') {
    midcom_tests_selenium_all::main();
}
?>