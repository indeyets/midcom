<?php
/**
 * Created on Nov 13, 2005
 * @author tarjei huse
 * @package no.bergfald.objectbrowser
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */
require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

//require_once '../../.././tests/config/cli_config.php';
$curr_path = dirname(__FILE__);
require_once $curr_path . '/../schema.php';
$GLOBALS['testclasses'] = array ('no_bergfald_objectbrowser_tests_schmas' => 0);
 
class no_bergfald_objectbrowser_tests_schmas extends UnitTestCase {

    /**
     * Add any objects you think should be leaves here
     */
    var $leaves = array();
    /**
     * Add any objects you think should be nodes here
     */
    var $nodes  = array();
    
    var $noups = array (
                        'midgard_host' => '',
                        'midgard_group' => '',
                        'midgard_member' => '',
                        'midgard_pagelink' => '',
                        'midgard_parameter' => '',
                        'midgard_person' => '',
                        'midgard_attachment' => '',
                        );
    
    function test_schemafunctions() {
        $schema = $this->get_simple_schema();
        //var_dump($schema->_schemas);
        foreach ($_MIDGARD['schema']['types'] as $type => $val) {
            $this->assertTrue($schema->classify_objecttype($type), "Classiffy_objecttype returned false for $val !");
            $this->assertTrue(is_array($schema->get_schema($type)), "Get_schema for $val does not return an array!");
            
            $this->assertTrue(is_array($schema->list_schemas($type)), "Get_schema for $val does not return an array!");
            $obj = new $type();
            
            $schema->set_object($obj);
            if (!array_key_exists($type, $this->noups)) {
                if ( 1 ||$schema->is_node($type)  ) {
                    if (!$this->assertTrue( is_string($schema->get_up_attribute($type) ) , "Missing up attribute for NODE $type " . $schema->get_up_attribute($type) )) {
                        var_dump($schema->_meta[$type]);
                    }
                    print "$type . <br/>";
                    var_dump($schema->get_up_attribute($type));
                } else {
                    if (! $this->assertTrue( is_string($schema->get_leaf_up_attribute($type) ) , "Missing up attribute for $type") ) {
                           var_dump($schema->_meta[$type]);
                    }
                }
            }
            /*
            print "$type: <br/>" ;
            var_dump($schema->is_node());
            var_dump($schema->is_node(&$obj));
            */
        }
        
        //var_dump($schema->get_children('midgard_style'));
        
        
         //   var_dump($schema->get_schema('midgard_element'));
    }
    
    function test_get_storage() {
    
        $schema = $this->get_simple_schema();
        //var_dump($schema->_schemas);
        foreach ($_MIDGARD['schema']['types'] as $type => $val) {
            $this->assertTrue(is_string($schema->get_storage($type)), "Cannot get storage for $type");
            var_dump($schema->get_storage($type));
        }
        
        // todo: add an extra schema that's not a normal type.
    }
    
    function get_simple_schema($objecttype = false) {
        $schema = no_bergfald_objectbrowser_schema::get_instance();
        if ($objecttype) {
            $object = new $objecttype();
            $schema->set_object(&$object);
        }
        
        return $schema;
    }
    
}