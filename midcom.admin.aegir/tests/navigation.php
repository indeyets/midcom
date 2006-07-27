<?php

/**
 * @package midcom.tests
 * 
 * Tests if the aegir_navigation class delivers the results it should.
 * 
 */

$base = dirname( __FILE__) . '/';
require_once $base . '../aegir_navigation.php';
require_once $base . 'config.php';

/**
 * Tests if the aegir_navigation class delivers the results it should.
 */
class midcom_admin_aegir_tests_navigation extends UnitTestCase{

    /**
     *  Array of ids that may be used to test (get_|list_)leaf()
     * @var array
     * */
    var $leaves = array();
    /**
     * Array of nonexistant ids that should return false in get_ functions.
     * @var array
     */
    var $non_leaves = array();
    
    /**
     * Array of ids that may be used to test (get_|list_)node
     * @var array
     */
    var $nodes = array();
    
    /**
     * Array of nonexistant ids that should return false in get_ functions.
     * @var array
     */
    var $non_nodes = array();
    
    /**
     * Instance of the navigation object
     * @var midcom_admin_aegir_module_navigation subclass
     */
     var $nav = null;
     
     /**
      * print some extra debugging messages?
      * Set this in setUp() while debugging.
      * @var boolean  
      */
      var $verbose = false;
    
    /**
     * Configuration object
     * @see midcom_admin_aegir_tests_config
     */  
    var $config = null;
    
    function midcom_admin_aegir_tests_navigation ($label = false, &$config) {
        $this->config = &$config;
    }
    
    function testNavNotNull() {
        $this->assertTrue($this->nav !== null, "this->nav is null, cannot run other tests");
        return;
    }
    
    function testIfRootReturnsNull() {
        if ($this->verbose) print "Testing " . __FUNCTION__ . "<br />";
       $this->assertTrue($this->nav->get_root_node() === '0', "Get root node returns the wrong value. It returns: " . $this->nav->get_root_node() );  
    }
    
    function testListRootNodes() {
        if ($this->verbose) print "<p>Testing " . __FUNCTION__ . "</p>";
        $root_nodes = $this->nav->list_nodes('0'); // root level
        $this->nodes = $root_nodes;
        if ($this->verbose) {
            print "found root-nodes: <br />";
            var_dump($root_nodes);
        }
        if (!$this->assertTrue(is_array($root_nodes), "A root node listing should return an array! It returned: " . gettype($root_nodes) ) ) {
            return;
        }
        if (!$this->assertTrue ( count($root_nodes) > 0 , "A root listing should show more than 0 nodes." . count($root_nodes) )) {
            return;
        }
        
        foreach ($root_nodes as $key => $id) {
            $node = $this->nav->get_node($id);
            $this->assertNavArrayCorrect($node,$id);
            
            
        }
        
    }
    
    function testListLeaves() 
    {
        $this->assertListLeaves('0');
    }
    
    function assertListLeaves( $root = '0', $level = 0) 
    {
        if ($this->verbose) {
             print "<b>Testing " . __FUNCTION__ . " with root: $root</b> <br />";
        }
        
        $nodes = $this->nav->list_nodes($root); 
        if ($this->verbose) {
            print count($nodes) . " nodes found: (level: $level) <br />";
            //var_dump($nodes);
        }
        if (!$this->assertTrue((is_array($nodes) || (is_bool($nodes) && !$nodes) ), 
            "A node listing should return an array or false! It returned: " . gettype($nodes) ) ) {
            return;
        }
        
        
        
        if ($nodes) foreach ($nodes as $key => $id) {
            
            $node = $this->nav->get_node($id);
            if ($this->assertNavArrayCorrect($node,$id)) {
                $this->assertGetLeaves($id, $node[MIDCOM_NAV_NAME]);
                
                if ($level < 4) {
                    $this->assertListLeaves($id, $level++);
                }
                
            } else {
                echo "Did not recurse below $level (node: $root)<br/>";
            }

        }
    }
    
    function assertGetLeaves ($node, $node_name) 
    {
        
        $leaves = $this->nav->list_leaves($node);
        if ($this->verbose) {
            print "<p><b>Listing leaves for node: $node, $node_name</b><br/>Leaves found: </p>";
        }
        
        $this->assertTrue( (is_array($leaves) || (is_bool($leaves) && !$leaves) ), "List leaves should return an array or false");
        //$this->assertFalse(count($leaves) == 0, "list_leaves should not return an empty array.");
        if ($this->verbose) {
            var_dump($leaves);
        }        
        if ($leaves ) foreach ($leaves as $key => $leaf) {
            $this->assertGetLeaf($leaf);
        }
        
    }
    
    function notestGetLeaf() 
    {
        if ($this->verbose) {
            print "Testing get_leaf <br />";
            var_dump ($this->leaves);
        }
        foreach ($this->leaves as $key => $id) {
            $this->assertGetLeaf($id);            
        }
    }
    
    function assertGetLeaf($id) 
    {
        $leaf = $this->nav->get_leaf($id);
        if ($this->verbose) {
            print "<p><b>Testing get_leaf $id returned: </b></p>";
            var_dump($leaf);
        }        
        $this->assertNavArrayCorrect($leaf, $id);
    }

    function testGetNode() 
    {
        if ($this->verbose) print "testGetNode: <br /> " ;
        
        foreach ($this->nodes as $id) {
            
            $node = $this->nav->get_node($id);
            if ($this->verbose) {
                print "Testing get_node on id $id <br />";
                var_dump($node);
            }
            if (!$this->assertTrue((is_array($node) || (is_bool($node) && !$node) ), 
                "A node listing should return an array or false! It returned: " . gettype($node) )) {
                return;   
            } 
            $this->assertNavArrayCorrect($node ,$id);
        }
    }
    /**
     * checks for values needed in both nodes and leaves.
     */
    function assertNavArrayCorrect($nav, $id) 
    {
        if (
            $this->assertTrue(is_array($nav), 
            "the nav object with id $id does not return an array as it should. It returned: " . gettype($nav)))
            {
                // now we can check the other issues
                $this->assertTrue(array_key_exists(MIDCOM_NAV_ID, $nav), 
                        "nav $id is missing MIDCOM_NAV_ID");
                $this->assertTrue(array_key_exists(MIDCOM_NAV_NAME, $nav), 
                        "nav $id is missing MIDCOM_NAV_NAME");
                $this->assertTrue(array_key_exists(MIDCOM_NAV_GUID, $nav), 
                        "nav $id is missing MIDCOM_NAV_GUID");
                $this->assertTrue(array_key_exists(MIDCOM_NAV_COMPONENT, $nav), 
                        "nav $id is missing MIDCOM_NAV_COMPONENT");
                $this->assertTrue(array_key_exists(MIDCOM_NAV_ICON, $nav), 
                        "nav $id is missing MIDCOM_NAV_ICON");
                return true;
        }
        return false;
    }

}
