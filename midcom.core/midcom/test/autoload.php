<?php
/**
 *
 */
$base = dirname(__FILE__);
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'PEAR.php';
require_once $base . '/../../autoload.php';

class AutoloaderTest extends PHPUnit_Framework_TestCase
{

    function test_autoloader_regexp_with_brace_on_separate_line() {
        $teststring = "
    /**
 * Active Calendar is PHP Class, that generates calendars (month or year view) as a HTML Table (XHTML-Valid).
 * http://www.micronetwork.de/activecalendar/
 * Available under LGPL license
 * 
 * Startup loads main class, which is used for all operations.
 * 
 * @package se.anykey.activecalendar
 */
class se_anykey_activecalendar_interface extends midcom_baseclasses_components_interface
{
    
    function se_anykey_activecalendar_interface()
    {   
        ";
        $class = new SmartLoader();
        
        $result = array();
        $res = preg_match_all( $class->classRegularExpression, $teststring, $result );
        //$res = preg_match_all( "%(interface|class)\s+([\w]+)\s+(extends\s+(\w+)\s+)?(implements\s+\w+\s*(,\s*\w+\s*)*)?%", $buf, $result = array());



        var_dump($class->classRegularExpression);
        var_dump($result);
        $this->assertEquals(1, $res);
        $this->assertEquals('se_anykey_activecalendar_interface', $result[2][0]);


        
    }

    
   
}
class Autoloader_tests 
{
    public static function main()
    {
    }
 
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('MidCOM Autoloader tests');
        $suite->addTestSuite('AutoloaderTest');
        return $suite;
    }
}
//urlfactorytests::main();

PHPUnit_TextUI_TestRunner::run(Autoloader_tests::suite());
?>
