<?php
/**
 * Created on Oct 30, 2005
 * @author tarjei huse
 * @package midcom.tests 
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */


//require_once '../../.././tests/config/cli_config.php';
$curr_path = dirname(__FILE__);
require_once $curr_path . '/../stylefinder.php';
require_once $curr_path . '/../toolbarfactory.php';

require_once MIDCOM_ROOT . '/midcom/tests/lib/toolbar.php';

$GLOBALS['testclasses'] = array ('styleeditor_test_toolbar' => 1);
 
//$GLOBALS['testconfig'] = 'midcom_admin_aegir_tests_config'; 
class styleeditor_test_toolbar extends midcom_tests_lib_toolbar {

    var $pages = array ( 1 => array ( 'page' => 'aeeb435d6e7fdad9e171b20be4844bc8' ));
    var $topics = array ( 
                    1 => array ( 
                            'topic' => '0000000011ba31d8b831e757d11e1948',
                            'host'  => '0000000025de7f42e72deac6841e4bdd',
                        )
                    );
                    
               
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
    

    function test_loading_toolbar() {
    
        $finder = $this->get_basic_finder();
        
        $toolbar = new midcom_admin_styleeditor_toolbarfactory(&$finder);
        
        $this->assertTrue(is_a($toolbar,'midcom_admin_styleeditor_toolbarfactory'), "The toolbar was not created");
    }
    
    function test_render_toolbar() {
        $finder = $this->get_basic_finder();
        
        $toolbar_factory = new midcom_admin_styleeditor_toolbarfactory(&$finder);
        $toolbar_factory->generate_toolbar();
        $toolbar = $toolbar_factory->get_toolbar();
        $this->assert_that_toolbar_is_correctly_defined($toolbar);
        //var_dump($toolbar);
        //echo $toolbar->render();
        
        
    }
    
    function test_normal_toolbar() {
        $finder = $this->get_basic_finder();
        $toolbars =& midcom_helper_toolbars::get_instance();
        
        $toolbar_factory = new midcom_admin_styleeditor_toolbarfactory(&$finder);
        $toolbar_factory->set_toolbar(&$toolbars->bottom);
        
        $toolbar_factory->generate_toolbar();
        $this->assert_that_toolbar_is_correctly_defined($toolbars->bottom);
        echo $toolbars->render_bottom();
    }
    
    function test_null_toolbar() {
        $finder = $this->get_basic_finder();
        
        $toolbar_factory = new midcom_admin_styleeditor_toolbarfactory(&$finder);
        
        $toolbar_factory->generate_toolbar();
        $this->assert_that_toolbar_is_correctly_defined($toolbar_factory->get_toolbar());
        //echo $toolbars->render();
    }
    
    function get_basic_finder() {
                 
        $test_host = new midgard_host();
        $test_host->get_by_guid('000000000ae8efa72efe53ef6d04e7cf');
        $topic = $test_host->parameter('midcom_template', 'root_topic');
        //print_r($test_host);
        
        $finder = false;
        $finder = $this->get_finder($topic ,$test_host);
        
        return $finder;        
    }
    function get_finder($topic = false, $page = false) {
        
        $finder = midcom_admin_styleeditor_stylefinder::factory();
        $finder->set_page($page);
        $finder->set_topic (new midcom_db_topic($topic));
        return $finder;
    }

}


/* brukes for å kunne integrere i flere filer */
if (realpath($_SERVER['PHP_SELF']) == __FILE__) {
    $test = new style_finder_test(&$config);
    $test->run(new TextReporter);
}

