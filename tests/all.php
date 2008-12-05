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

if (! defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'midcom_tests_all::main');
}
if (! defined('COMPONENT_DIR')) {
    define('COMPONENT_DIR', dirname(__FILE__).'/../');
}

if (! defined('MIDCOM_CONFIG')) {
    define('MIDCOM_CONFIG', dirname(__FILE__).'/../midcom_core/configuration/defaults.yml');
}
if (! defined('MIDGARD_CONFIG')) {
    define('MIDGARD_CONFIG', 'midgard');
}
if (! defined('MIDCOM_TESTS_LOGLEVEL'))
{
    define('MIDCOM_TESTS_LOGLEVEL', 'warn');
}
if (! defined('MIDCOM_TESTS_SITEGROUP'))
{
    define('MIDCOM_TESTS_SITEGROUP', 1);
}
if (! defined('MIDCOM_TESTS_ENABLE_OUTPUT')) {
    define('MIDCOM_TESTS_ENABLE_OUTPUT', false);
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

$old_path = ini_get('include_path');

ini_set('include_path', COMPONENT_DIR.PATH_SEPARATOR.$old_path);

/**
 * @package midcom_tests
 */
class midcom_tests_all
{   
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
    
    public static function get_components_with_tests()
    {
        $skip = array( '.' , '..', 'scaffold', '.DS_Store', '.git', 'static', 'build', 'build.properties', 'build.xml' );
        $skip = array_flip($skip);
        $components = array();
        
        $files = dir(COMPONENT_DIR);

        if (! $files) 
        {
            throw new Exception('get_components_with_tests: No components found in ' . COMPONENT_DIR);
        }
        
        while (($file = $files->read()) !== false) 
        {
            if (   array_key_exists($file, $skip)
                || substr($file, 0, 1) == '.') 
            {
                continue;
            }

            if (file_exists(COMPONENT_DIR . "/{$file}/tests/all.php")) 
            {
                require_once COMPONENT_DIR . "/{$file}/tests/all.php";
                $name = "{$file}_tests";
                $components[$name] = "{$name}_all";                
            }      
        }
        
        return $components;
    }
 
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('MidCOM');
        $components = midcom_tests_all::get_components_with_tests();
        foreach ($components as $component => $test_name)
        {
            $suite->addTest(
                call_user_func(array($test_name, 'suite'))
            );
        }
 
        return $suite;
    }
}
 
if (PHPUnit_MAIN_METHOD == 'midcom_tests_all::main') {
    midcom_tests_all::main();
}
?>