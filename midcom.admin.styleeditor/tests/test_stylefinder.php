<?php
require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

//require_once '../../.././tests/config/cli_config.php';
$curr_path = dirname(__FILE__);
require_once $curr_path . '/../stylefinder.php';
/**
 * Created on Oct 30, 2005
 * @author tarjei huse
 * @package midcom.tests 
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */

$GLOBALS['testclasses'] = array ('style_finder_test' => 1); 
//$GLOBALS['testconfig'] = 'midcom_admin_aegir_tests_config'; 
class style_finder_test extends UnitTestCase {

    var $pages = array ( 1 => array ( 'page' => 'aeeb435d6e7fdad9e171b20be4844bc8' ));
    var $topics = array ( 
                    1 => array ( 
                            'topic' => '0000000011ba31d8b831e757d11e1948',
                            'host'  => '0000000025de7f42e72deac6841e4bdd',
                        )
                    );
                    
    var $finder = null;
    
    
    function style_finder_test ($label = false, $config) {
        parent::UnitTestCase($label);
    }
    function setUp() {
        
    }
    
    function tearDown() {}
    
    function test_loading_style_finder() {
        $finder = null;      
        $finder = $this->get_basic_finder();
        
        $this->assertTrue( is_object($finder) == true, "Finder factory doesnæt workas object was missing") ;
        $this->assertTrue( is_object($finder->_page) == true, "Finder missing _page");
        $this->assertTrue( is_object($finder->_page->_page) == true, "Finder missing _page->_page");
        $this->assertTrue( is_object($finder->_midcom) == true, "Finder missing _midcom") ;
        $this->assertTrue( is_object($finder->_topic) == true, "Finder missing _topic") ;
    }
    
    function test_getting_style_stack() {
        $finder = $this->get_basic_finder();
        //if (!$this->assertTrue($finder, "Finder not created as object was missing") ) return;
        
        $stylestack = $finder->get_style_stack(); 
        
        $this->assertTrue(is_array($stylestack), "The stylestack is not an array");
                
        //var_dump($stylestack);
        
        
    }
    
    function test_create_style_overview() {
    
        $finder = $this->get_basic_finder();
        $stylestack = $finder->get_style_elements();
        
        $this->assertTrue(is_array($stylestack), "The stylestack is not an array");
                
        //var_dump($stylestack);
    }
    
    
    function test_get_stylelisting() {
    
        $finder = $this->get_basic_finder();
        //if (!$this->assertTrue($finder, "Finder not created as object was missing") ) return;
        
        $stylestack = $finder->get_style_elements(); 
        
        $this->assertTrue(is_array($stylestack), "The stylestack is not an array");
                
    
    }
    
    
    function get_basic_finder() {
        
        /**
         * search for a micom host to test on...
         */
         
        if (0){
            $qb = new MidgardQueryBuilder('midgard_host');
            $hosts = $qb->execute();
            foreach ($hosts as $key => $host) {
                print $host->name . $host->prefix .": {$host->guid}<br/>";
                print $host->parameter('midcom_template', 'root_topic') . "<br/>";
                if (( $topic = $host->parameter('midcom_template', 'root_topic') ) != "" ) {
                    $test_host = $host;
                    break;
                    
                }
            }
        }
        
        $test_host = new midcom_db_host();
        $test_host->get_by_guid('000000000ae8efa72efe53ef6d04e7cf');
        $topic = $test_host->parameter('midcom_template', 'root_topic');
        
        $this->_finder = $this->get_finder();
        
        $this->assert_set_topic($topic);
        $this->assert_set_host($test_host->guid);
        $this->assert_set_cache();
        return $this->_finder;
    }
    
    function test_set_host() {
        $this->get_finder();
        $this->assert_set_host('000000000ae8efa72efe53ef6d04e7cf');
    }
    
    function assert_set_style($style_id) {
        $style = new midcom_db_style($style_id);
        
        $this->_finder->set_style($style);
        $this->assertTrue($this->_finder->_style  !== null, "Set_style didn't work with " . $style->name);
        
    }
    
    function assert_set_host( $host) {
        $test_host = new midcom_db_host($host);
        $this->_finder->set_page($test_host);
        $this->assertTrue($this->_finder->_midcom !== null, "set host should set midcom" );
        $this->assertTrue($this->_finder->_style  !== null, "set host should set style");
    }
    
    
    function assert_set_topic($topic) 
    {
        $td = new midcom_db_topic($topic);
        $this->_finder->set_topic(&$td);
        $this->assertTrue($this->_finder->_topic  !== null, "Topic is not set: " . $td->guid);
        $this->assertTrue($this->_finder->_midcom !== null, "Topic sets midcom");
        //$this->assertTrue($this->_finder->_style  !== null, "Topic sets style" . $td->name);
    }
    
    function assert_set_cache() 
    {
        $cache = array();
        $this->_finder->set_cache(&$cache);
    }
    
    function get_finder($topic = false, $page = false) 
    {
        
        $finder = midcom_admin_styleeditor_stylefinder::factory();
        return $finder;
    }

}


/* brukes for å kunne integrere i flere filer */
if (realpath($_SERVER['PHP_SELF']) == __FILE__) {
    $test = new style_finder_test(&$config);
    $test->run(new TextReporter);
}

