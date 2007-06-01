<?php
/**
 * Starting Aegir nav class. I'm not sure how perfect it is.
 * Created on Aug 3, 2005
 * @package midcom.tests
 */
 
class midcom_tests_aegir_navigation extends midcom_admin_aegir_module_navigation  {


    /**
     * Id of the current node.
     * @access private
     * @var string
     */
    var $_current_node = null;
    /**
     * Id of current leaf
     *  @access private
     * @var string
     */
    var $_current_leaf = null;
    
    /**
     * Id of tree_root (where applicable)
     * @access private
     * @var string
     */
    var $_root_node = 0;

    /**
     * Reads a node data structure from the database
     * 
     * @param mixed 
     * 		int $id The ID of the topic for which the NAP information is requested.
     * 		midcom_baseclasses_database_topic
     * @return Array Node data structure 
     * @access public
     */

	function get_node($id) {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        
        if ( !array_key_exists($id, $_MIDCOM->componentloader->manifests)) {
            
            return false;
        } else {
            
    		// Now complete the node data structure, we need a metadata object for this:
        	//$metadata =& midcom_helper_metadata::retrieve($topic);
            $manifest = &$_MIDCOM->componentloader->manifests[$id];
            
            $nodedata[MIDCOM_NAV_NAME] = $manifest->get_name_translated();
            $nodedata[MIDCOM_NAV_URL] = $id;
            $nodedata[MIDCOM_NAV_GUID] = null;
            $nodedata[MIDCOM_NAV_ID] = $id;
            $nodedata[MIDCOM_NAV_TYPE] = 'node';
            $nodedata[MIDCOM_NAV_OBJECT]  = null;
            $nodedata[MIDCOM_NAV_ICON]  = null;
            $nodedata[MIDCOM_NAV_COMPONENT] = 'tests';
        
            debug_pop();
            return $nodedata;
        }
	}  
	/**
     * This returns the testfile as a nav object
     * @param string $leaf_id	the _URLENCODED_ path to a testfile 
     * @return Array		The leaf-data as outlined in the class introduction, false on failure
     */
	function get_leaf($file) {
        if (!file_exists( $file)) {
            return false;
        }
            
        $filename  = basename($file, '.php');
        
        if (substr($filename,0,5) != 'test_') {
            return false;
        }
        
        $dir       = dirname($file);
        // make a url that is usable for navigation..
        
        $dirs = explode('/', $dir);
        // the -1 is to remove the tests dir.
        // I set the fisrt outside the for loop to ensure we do not get an
        // ending . Like de.linkm.taviewer. 
        $component = $dirs[count($dirs) -2];
        
        for ($i = count($dirs) - 3; $i > 0 && $dirs[$i] != 'lib'; $i--) {
            if ($dirs[$i] != '') {
                $component =  $dirs[$i] . "." . $component;
            }
             
        }
        $nodedata[MIDCOM_NAV_NAME] = substr($filename, 5, strlen($filename));
        $nodedata[MIDCOM_NAV_URL] = $component . '/' . $filename;
        $nodedata[MIDCOM_NAV_GUID] = null;
        $nodedata[MIDCOM_NAV_ID] = $file;
        $nodedata[MIDCOM_NAV_TYPE] = 'leaf';
        $nodedata[MIDCOM_NAV_OBJECT]  = null;
        $nodedata[MIDCOM_NAV_ICON]  = null;
        $nodedata[MIDCOM_NAV_COMPONENT] = 'tests';
        
        return $nodedata;
	}
	/**
     *. There are no nodes as we do not organize them by component or type yet.
     *  TODO: return component listing or type listing.
     */
	function list_nodes($node_up = '0') {
        
        if ($node_up !== '0') 
        {
            return array();
        }
        $i = 0;
        $ret = array();
        foreach ($_MIDCOM->componentloader->manifests as $key => $manifest) {
            $path = MIDCOM_ROOT . '/'. str_replace('.', '/', $manifest->name) . "/tests/";
            if (file_exists($path) ) {
                
                $ret[$i++] = $manifest->name;
            }
        }
        
        return $ret;
	}
    /**
     * node_up is a midcom component path. 
     */
	function list_leaves ($node_up = '0' ) {
        // we got no toplevel nodes for now...
		if ($node_up == '0') {
			return array();
		}
        $i = 0;
		$files = array();
        
        
        $path = MIDCOM_ROOT . '/'. str_replace('.', '/', $node_up) . "/tests/";
        //print $path . "<br/>";
        //print_r($manifest); 
        if (file_exists($path)) {
            $dir_open = @ opendir($path);
            while (($dir_content = readdir($dir_open)) !== false) {
                //print $path.$dir_content . "<br/>";
                if (strlen($dir_content) > 5 && substr($dir_content,0,5) == 'test_' 
                    && is_readable($path . '/' . $dir_content)) {
                    //$files[$manifest][] = $dir_content;
                    $files[$i++] = $path . '/' . $dir_content;
                    
                }
            }
        }
        
        
		return $files;
	}
    
    /**
     * All leaves are linked to the root. 
     * 
     * @param string $leaf_id   The Leaf-ID to search an uplink for.
     * @return int          The ID of the Node for which we have a match, or false on failure.
     * @see midcom_helper__basicnav::get_leaf_uplink()
     */
    function get_leaf_uplink($leafid) {
        $leaf = $this->get_leaf($leafid);
        if (!$leaf) {
            return false;
        } 
        $url = split ('/', $leaf[MIDCOM_NAV_URL]);
        
        return $url[0];
    }
    /**
     * Returns the ID of the node to which $node_id is assosiated to, false
     * on failure. The root node's uplink is -1.
     * 
     * @param int $node_id  The Leaf-ID to search an uplink for.
     * @return int          The ID of the Node for which we have a match, -1 for the root node, or false on failure.
     * @see midcom_helper__basicnav::get_node_uplink()
     */
    function get_node_uplink($nodeid) 
    {
                
        $node = $this->get_node($nodeid);
        if (!$node) {
            return false;
        }
        // all nodes are linked to the root for now.
        return  -1;
    }
}
?>
