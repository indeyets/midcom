<?php
/**
 * Created on Aug 3, 2005
 * base class for providing navigation within Aegir.
 * @package midcom.admin.aegir
 * 
 * 
 */
 
class midcom_admin_aegir_module_navigation {


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
    var $_root_node = null;

    /** 
     * the local   
     * */
    var $_config = null;
    /**
     * the host of the current request - if set.
     * This is used by ais, but also several other components to define a 
     * host context for the request.
     * @acces private
     */
    var $_host = null;
   
    /**
     * Cosntructor. Empty for now
     */
    function midcom_admin_aegir_module_navigation ()
    {
    
    }
    /**
     * What the root node is and how it is defined might be different for different 
     * handlers. Therefore we need a way to check this.
     * @return string node_id 
     */
    function get_root_node() 
    {
        return '0';
    }
    /**
     * Check if a node_id is the root node of the tree.
     * @return boolean true if node_id is root node.
     * @param string node_id 
     */
    function is_root_node( $node_id) 
    {
        return $node_id == 0;
    
    }


    /**
     * Reads a node data structure from the database
     * 
     * The Node must contain the following:<br />
     * MICOM_NAV_ID      the internal id for this module , usually a guid from the object.<br />
     * MIDCOM_NAV_URL    the link into the module. the componentname will be appended to this.<br />
     * MIDCOM_NAV_NAME   the name of the object<br />
     * MIDCOM_NAV_COMPONENT the aegir module id <br />
     * MIDOM_NAV_TYPE   should just be set to node<br />
     * .
     *
     * Sample:
     * <pre>
     *  $nodedata[MIDCOM_NAV_NAME] = $object->name;
     *   $nodedata[MIDCOM_NAV_URL] = $object->guid;
     *   $nodedata[MIDCOM_NAV_ADMIN][MIDCOM_NAV_URL] = "objectbrowser/". $object->guid;
     *   $nodedata[MIDCOM_NAV_ADMIN][MIDCOM_NAV_NAME] = $object->{$name_attribute};
     *   $nodedata[MIDCOM_NAV_GUID] = $object->guid;
     *   $nodedata[MIDCOM_NAV_ID] = $object->guid;
     *   $nodedata[MIDCOM_NAV_TYPE] = 'node';
     *   //$nodedata[MIDCOM_NAV_SCORE] = $object->score;
     *   $nodedata[MIDCOM_NAV_OBJECT]  = &$object;
     *   
     *   $nodedata[MIDCOM_NAV_COMPONENT] = 'objectbrowser';
     * </pre> 
     * 
     * 
     * @param mixed 
     * 		int $id The ID of the topic for which the NAP information is requested.
     * 		midcom_baseclasses_database_topic
     * @return Array Node data structure or false on failure
     * @access public
     */

	function get_node($id) 
    {
   	    return false;	
	}
	/**
     * This will give you a key-value pair describeing the leaf with the ID 
     * $node_id. 
     * The defined keys are described above in leaf data interchange
     * format. You will get false if the leaf ID is invalid.
     * 
     * The Leaf must contain the following:
     * MICOM_NAV_ID      the internal id for this module , usually a guid from the object.
     * MIDCOM_NAV_URL    the link into the module. the componentname will be appended to this.
     * MIDCOM_NAV_NAME   the name of the object
     * MIDCOM_NAV_COMPONENT the aegir module id 
     * MIDOM_NAV_TYPE   should just be set to leaf.
     *
     * Sample:
     * <pre>
     *  $leaf[MIDCOM_NAV_NAME] = $object->name;
     *   $leaf[MIDCOM_NAV_URL] = $object->guid;
     *   $leaf[MIDCOM_NAV_ADMIN][MIDCOM_NAV_URL] = "objectbrowser/". $object->guid;
     *   $leaf[MIDCOM_NAV_ADMIN][MIDCOM_NAV_NAME] = $object->{$name_attribute};
     *   $leaf[MIDCOM_NAV_GUID] = $object->guid;
     *   $leaf[MIDCOM_NAV_ID] = $object->guid;
     *   $leaf[MIDCOM_NAV_TYPE] = 'leaf';
     *   //$leaf[MIDCOM_NAV_SCORE] = $object->score;
     *   $leaf[MIDCOM_NAV_OBJECT]  = &$object;
     *   
     *   $leaf[MIDCOM_NAV_COMPONENT] = 'objectbrowser';
     * </pre> 
     * 
     * 
     * @param string $leaf_id	The leaf-id to be retrieved.
     * @return Array		The leaf-data as outlined in the class introduction, false on failure
     */
	function get_leaf($leaf_id) 
    {
		debug_push_class(__CLASS__, __FUNCTION__);
		
		
		debug_pop();
        return false;
	}
	
    /*
     * List nodes, returns a list of nodeids below the current node.
     * Note, what the nodeid is depends on the component.
     * 
     * The value 0 has the special meaning of "root_node".
     * 
     * @param string id of node above
     * @return array list of nodeids
     * */
	function list_nodes($node_up = 0) 
    {
		return array();
	}
    
    /**
     * Returns all leaves for the current content topic.
     * 
     * It will hide the index leaf from the NAP information unless we are in Autoindex
     * mode. The leaves' title are used as a description within NAP, and the toolbar will
     * contain edit and delete links.
     * @params string id of parentnode.
     * @return array list of leafids.
     */ 
     
	function list_leaves ($node_up) 
    {
		debug_push_class(__CLASS__, __FUNCTION__);
        debug_pop();
        return array();
	}
    
    /**
     * Returns the ID of the node to which $leaf_id is accociated to, false
     * on failure.
     * 
     * @param string $leaf_id   The Leaf-ID to search an uplink for.
     * @return int          The ID of the Node for which we have a match, or false on failure.
     * @see midcom_helper__basicnav::get_leaf_uplink()
     */
    function get_leaf_uplink($leafid) 
    {
        return false;
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
        // todo add propper warnings if these functions are not implemented.
        return false;
    }
    
    /**
     * Set the current_node
     * Use this function if you cannot go via request_data.
     * @param id of current node
     */
    function set_current_node($id) 
    {
        $this->_current_node = $id;
    }
    
    /**
     * get the current node.
     * @return mixed string nodeid or null.
     */
    function get_current_node() 
    {
        if ($this->_current_node === null && $this->_current_leaf !== null ) {
            $this->_current_node = $this->get_leaf_uplink($this->_current_leaf);
        }
        return $this->_current_node;
    }
    /**
     * get the current leaf
     * @return mixed string leafid or null
     */
    function get_current_leaf( ) 
    {
        return $this->_current_leaf;
    }
    
    /**
     * Set the root_node
     * Use this function if you cannot go via request_data.
     * @param id of current node
     */
    function set_root_node($id) 
    {
        $this->_root_node = $id;
    }
    /**
     * Set the current leaf
     * Use this function if you cannot go via request_data.
     * @param id of current node
     */
    function set_current_leaf($id) 
    {
        $this->_current_leaf = $id;
    }
   
    /**
     * Construct a breadcrumb line.
     * 
     * Note: This constructs the part after the component line.
     * Gives you a line like "Start > Topic1 > Topic2 > Article" using NAP to
     * traverse upwards till the root node. $separator is inserted between the
     * pairs, $class, if non-null, will be used as CSS-class for the A-Tags.
     * 
     * The parameter skip_levels indicates how much nodes should be skipped at 
     * the beginning of the current path. Default is to show the complete path. A
     * value of 1 will skip the home link, 2 will skip the home link and the first
     * subtopic and so on. If a leaf or node is selected, that normally would be
     * hidden, only its name will be shown.
     * 
     * @param int       $skip_levels    The number of topic levels to skip before starting to work (use this to skip "Home" links etc.).
     * @return array   The computed breadrumb array as a list
     */
    function get_breadcrumb_array($skip_levels = 0) 
    {
        //$request_data =& $_MIDCOM->get_custom_context_data('request_data');
        //debug_push_class(__CLASS__, __FUNCTION__);
        $nodes = array();
        $node_up = null;
        if (!is_null($this->_current_leaf)) 
        {
            $nodes[0] = $this->get_leaf($this->_current_leaf);
            $node_up = $this->get_leaf_uplink($nodes[0][MIDCOM_NAV_ID]);
        }
        if ($node_up === null && !is_null($this->_current_node)) 
        {
            $node_up = $this->_current_node;
        }
              
        if ( is_null($node_up)) 
        {
          //  debug_pop();
            return array();
        }
        
         
        for ($i = count($nodes); $node_up > -1; $i++) 
        {
            $nodes[$i] =  $this->get_node($node_up);
            $node_up = $this->get_node_uplink($nodes[$i][MIDCOM_NAV_ID]);
             
        }
                
        //debug_pop();
        return $nodes;
    }
        /**
     * Construct a breadcrumb line. 
     * 
     * Note: This constructs the part after the component line.
     * Gives you a line like "Start > Topic1 > Topic2 > Article" using NAP to
     * traverse upwards till the root node. $separator is inserted between the
     * pairs, $class, if non-null, will be used as CSS-class for the A-Tags.
     * 
     * The parameter skip_levels indicates how much nodes should be skipped at 
     * the beginning of the current path. Default is to show the complete path. A
     * value of 1 will skip the home link, 2 will skip the home link and the first
     * subtopic and so on. If a leaf or node is selected, that normally would be
     * hidden, only its name will be shown.
     * 
     * @param string    $separator      The separator to use between the elements.
     * @param string    $class          If not-null, it will be assigned to all A tags.
     * @param int       $skip_levels    The number of topic levels to skip before starting to work (use this to skip "Home" links etc.).
     * @param string    $current_class  The class that should be assigned to the currently active topic. 
     * @return string   The computed breadrumb line.
     */
    function get_breadcrumb_line($separator = " &gt; ", $class = null, $skip_levels = 0, $current_class = null) 
    {
        $nodes = $this->get_breadcrumb_array($skip_levels);
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $result = "";
        while ( count($nodes) > 0 )
        {
            $node = array_shift($nodes);
            
            $result = "<a href=\"$prefix{$node[MIDCOM_NAV_ADMIN][MIDCOM_NAV_URL]}\"" . (is_null($class) ? "" : " class=\"$class\"") . ">"
              . htmlspecialchars($node[MIDCOM_NAV_NAME])

              . "</a>" . $separator . $result;
        }
           
        return $result;
    }
    
     /**
      * Get the current host 
      * @return mixed string hostname or null
      * @access public
      */
     function get_host() 
     {
        return $this->_host;
     }
}
?>