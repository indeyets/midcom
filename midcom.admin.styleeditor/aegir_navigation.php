<?php
/**
 * Created on Aug 3, 2005
 * @package midcom.admin.styleeditor
 */
 
class midcom_admin_styleeditor_aegir_navigation extends midcom_admin_aegir_module_navigation  {


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
     * A simple cache. 
     */    
    var $_cache = array();


    /**
     * Sitegroup of the user
     * 
     */
    var $sitegroup = 0;
    
    function midcom_admin_styleeditor_aegir_navigation () 
    {
        parent::midcom_admin_aegir_module_navigation();
        $user = $_MIDCOM->auth->user->get_storage();
        $this->sitegroup = $user->sitegroup;
    } 

    /**
     * Reads a node data structure from the database
     * 
     * @param mixed 
     * 		int $id The ID of the style for which the NAP information is requested.
     * 		midcom_baseclasses_database_style
     * @return Array Node data structure 
     * @access public
     */

	function get_node($id) {
        debug_push_class(__CLASS__, __FUNCTION__);
		
        if (array_key_exists($id, $this->_cache)) {
            $style = &$this->_cache[$id];
        } else {
		 $style = new midcom_db_style($id);
        }
		
        
        if (! $style)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Cannot load NAP information, aborting: Could not load the style {$id} from the database (" 
                . mgd_errstr() . ').');
            // This will exit().
        }
        debug_add("Trying to load NAP data for style {$style->name} (#{$style->guid})");		
       
		// Now complete the node data structure, we need a metadata object for this:
    	//$metadata =& midcom_helper_metadata::retrieve($style);
        $nodedata[MIDCOM_NAV_NAME] =  "{$style->name} ({$style->id})";
        $nodedata[MIDCOM_NAV_URL] = "style/". $style->guid .   '.html';
        $nodedata[MIDCOM_NAV_GUID] = $style->guid;
        $nodedata[MIDCOM_NAV_ID] = $style->guid;
        $nodedata[MIDCOM_NAV_TYPE] = 'node';
        $nodedata[MIDCOM_NAV_ICON] = null;
        $nodedata[MIDCOM_NAV_OBJECT]  = &$style;
        $nodedata[MIDCOM_NAV_COMPONENT] = 'styleeditor';
        
        debug_pop();
        return $nodedata;
		
	}
	/**
     * This will give you a key-value pair describeing the leaf with the ID 
     * $node_id. 
     * The defined keys are described above in leaf data interchange
     * format. You will get false if the leaf ID is invalid.
     * 
     * @param string $leaf_id	The leaf-id to be retrieved.
     * @return Array		The leaf-data as outlined in the class introduction, false on failure
     */
	function get_leaf($leaf_id) 
    {
        if (array_key_exists($leaf_id, $this->_cache)) {
            $element = &$this->_cache[$leaf_id];
        } else {
            $element = new midcom_db_element($leaf_id);
        }
		
		
		if (!$element) {
            return false;
		}
        
		return array 
                (
                	MIDCOM_NAV_URL => "element/{$element->guid}.html",
                    MIDCOM_NAV_ID => $element->guid,
                	MIDCOM_NAV_NAME => $element->name,
                    MIDCOM_NAV_GUID => $element->guid,
                    MIDCOM_NAV_TOOLBAR => array(),
                    MIDCOM_NAV_TYPE => 'leaf',
                    MIDCOM_NAV_COMPONENT => 'styleeditor',
                    MIDCOM_NAV_ICON => 'midcom.admin.aegir/document.png',
                    MIDCOM_NAV_OBJECT => &$element
                );
	}
	/**
     *.List the styles
     * @var guid of the parent or  
     */
	function list_nodes($node_up = '0') 
    {
		$qb = $_MIDCOM->dbfactory->new_query_builder('midcom_db_style');
		
        if ($node_up === '0') 
        {
            $node_up = 0;
        } 
        else 
        {
            $node_up = $this->_node_up_to_id($node_up);
        }
            
		$qb->add_constraint('up', '=',$node_up);
        $qb->begin_group('OR');
        //$qb->add_constraint('sitegroup', '=', $this->sitegroup);
        $qb->add_constraint('sitegroup', '<>', 0);
        $qb->add_constraint('name', 'like', "template_%");
        $qb->end_group();
		$result = $qb->execute();
        
		$nodes = array();        
		for ($i = 0 ; $i < count($result); $i++) {
            $this->_cache[$result[$i]->guid] = &$result[$i];
			$nodes[$i] = $result[$i]->guid;
		}
		return $nodes;
			
	}
	 /**
     * Leaf listing function, the default implementation returns an empty array indicating
     * no leaves. Note, that the active leaf index set by the other parts of the component
     * must match one leav out of this list.
 
     * 
     * @return Array NAP compilant list of leaves. 
     */
     
    /**
     * Returns all leaves for the current content style.
     * 
     * It will hide the index leaf from the NAP information unless we are in Autoindex
     * mode. The leaves' title are used as a description within NAP, and the toolbar will
     * contain edit and delete links.
     */
    function get_leaves($node_up = 0) 
    {
        
        return array();
    }
    /**
     * As we work with guids, but need ids, this simple function does
     * the mapping.
     */
    function _node_up_to_id($guid) 
    {
        if (array_key_exists($guid, $this->_cache) ) 
        {
            return $this->_cache[$guid]->id;
        }
        else 
        {
            $node_nav = $this->get_node($guid);
            if (!$node_nav ) {
                 $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Cannot list_leaves($node_up), aborting: Could not load the style {$node_id} from the database "); 
            }
            return $node_nav[MIDCOM_NAV_OBJECT]->id;
        }
    }
     
	function list_leaves ($node_up) {
		
		
        if ($node_up == '0') 
        {
            return array();
        }
        
        $node_id = $this->_node_up_to_id($node_up);
        
		$qb = $_MIDCOM->dbfactory->new_query_builder('midcom_db_element');
        //$qb = new MidgardQueryBuilder("midgard_element");
        $qb->add_constraint('style', '=', $node_id);
        /*
        $qb->begin_group('OR');
        $qb->add_constraint('sitegroup', '=', $this->sitegroup);
        $qb->add_constraint('sitegroup', '=', 0);
        $qb->end_group();
        */
        // The QB does not yet support ordering on the PHP level
        // $qb->add_order($sort);
        $result = $_MIDCOM->dbfactory->exec_query_builder($qb);
        
        if (is_null($result))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to query the content style, returning no leaves, see debug level log for details.', MIDCOM_LOG_INFO);
            debug_print_r('Content style was:', $this->_content_style);
            debug_pop();
            return Array();
        } 
        
        // Prepare everything
        $leaves = array ();
		$i = 0;
		foreach ($result as $element) {
            $this->_cache[$result[$i]->guid] = &$result[$i];
			$leaves[$i] = $element->guid;
            $i++;
		}
		return $leaves;
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
        
        $leaf = $this->get_leaf($leafid);
        if (!$leaf) {
            return false;
        } 
        return $leaf[MIDCOM_NAV_OBJECT]->style;
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
        if ($node[MIDCOM_NAV_OBJECT]->up == 0 ) {
            // we have reached the root of the tree.
            return -1;
        }
        return $node[MIDCOM_NAV_OBJECT]->up;
    }
    
    /**
     * Share the cache!
     * This returns a reference to the cache that nav uses so others can benefit from it too.
     * @return reference to the internal cache of nav.
     * 
     */
    function &get_cache () {
        return $this->_cache;
    }    
    
}
