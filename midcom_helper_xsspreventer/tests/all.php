<?php
/**
 * @package midcom_helper_xsspreventer
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'midcom_helper_xsspreventer_tests_all::main');
}

require_once('PHPUnit/Framework.php');
require_once('PHPUnit/TextUI/TestRunner.php');

require_once('tests/helpers.php');

/**
 * Run all XSS tests
 *
 * @package midcom_helper_xsspreventer
 */
class midcom_helper_xsspreventer_tests_all
{   
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
 
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('MidCOM midcom_helper_xsspreventer');
        
        $tests = midcom_core_tests_helpers::get_tests(__FILE__, __CLASS__);
        foreach ($tests as $test)
        {
            $suite->addTestSuite($test);
        }
 
        return $suite;
    }
}
 
if (PHPUnit_MAIN_METHOD == 'midcom_helper_xsspreventer_tests_all::main') 
{
    midcom_helper_xsspreventer_tests_all::main();
}
?>