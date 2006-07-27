<?php
/**
 * Created on Aug 3, 2005
 * @package midcom.admin.content2
 * 
 */
 /**
  * 
  * This is the aegir navigationclass for navigating a tree of component topics.
  * 
  */
class midcom_admin_content2_aegir_navigation extends midcom_admin_aegir_module_navigation  {


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
     * simple objectcache.
     * @access private
     * @var array
     * */
     
    var $_host = null;
    /**
     * objectcache, a simple cache of objects by guid
     */
    var $_cache = array();
    
    /**
     * leafcache -> this caches leaves by topic-id since the id's may be the 
     * same accross different topics.
     */
    var $_leafcache = array();

    /**
     * array of contexts allready in use. 
     */
    var $_contexts = array();

    function midcom_admin_content2_aegir_navigation () 
    {
        /* since nav requires this, we need to include it here, as the rest of the component
         * will not be started by aegir_nav if not needed.
         */
        
        require_once MIDCOM_ROOT . "/midcom/admin/content2/context.php";
    }

    /**
     * Reads a node data structure from the database
     * 
     * @param mixed 
     * 		int $id The ID of the topic for which the NAP information is requested.
     * 		midcom_baseclasses_database_topic
     * @param boolean if this is a hostid 
     * @return Array Node data structure 
     * @access public
     */

	function get_node($id) 
    {   
            
            $context = &$this->get_context($id);
            
            $context->enter_context();
            
            $nav = new midcom_helper_nav($context->get_current_context());
            
            $node = $nav->get_node($id);
            $context->leave_context();
            
            if (!$node) {
                return $node;
            }
            //$node[MIDCOM_NAV_URL] = 'topic/edit/' . $node[MIDCOM_NAV_ID]  ;
            $node[MIDCOM_NAV_URL]   = $node[MIDCOM_NAV_ID]  . "/data";
            $node[MIDCOM_NAV_COMPONENT] = 'ais';
            $node[MIDCOM_NAV_ICON] = null;
            return $node;
            
	}

	/**
     * This will give you a key-value pair describeing the leaf with the ID 
     * $node_id. 
     * The defined keys are described above in leaf data interchange
     * format. You will get false if the leaf ID is invalid.
     * 
     * @param string $leaf	The leaf-id to be retrieved.
     * @return Array		The leaf-data as outlined in the class introduction, false on failure
     */
	function get_leaf($leaf) 
    {
        list ($node_id, $leaf_id ) = explode ("-", $leaf);
        
        $node = $this->get_node($node_id);
        if (!$node ) 
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not find node for $leaf");
            debug_pop();
            return false;
        }
                
        $context = &$this->get_context($node[MIDCOM_NAV_OBJECT]->id);
        
        $context->enter_context();
        // code indented to visualize different context        
            
            $leaf_nav = $context->nav->get_leaf($leaf);
        
        $context->leave_context();
        
        if (!$leaf_nav) 
        {
            return false;
        }
        
        $leaf_nav[MIDCOM_NAV_URL] = $node[MIDCOM_NAV_ID] . "/data/" . $leaf_nav[MIDCOM_NAV_ADMIN][MIDCOM_NAV_URL];
        $leaf_nav[MIDCOM_NAV_COMPONENT] = $node[MIDCOM_NAV_COMPONENT];
        $leaf_nav[MIDCOM_NAV_ICON] = null;
        return $leaf_nav;
        
	}
    
	/**
     *. 
     */
	function list_nodes($node_up = '0') 
    {
        
        if ($node_up === '0' ) {
            return $this->_list_root_midcom_topics();    
        }
        
        $context = &$this->get_context($node_up);
        $context->enter_context();
            // indented to show different context            
            $nodes = $context->nav->list_nodes($node_up);
            
            if (! $nodes) 
            {
                $context->leave_context();   
                return false;
            }
            
        $context->leave_context();
        return $nodes;
	}
    /**
     * This function lists all the root topics that are midcom topics for a sitegroup
     */    
    function _list_root_midcom_topics () 
    {
        $return = array();
        $qb = $_MIDCOM->dbfactory->new_query_builder('midcom_baseclasses_database_topic');
        $qb->add_constraint('up', '=', 0);
        if ($_MIDGARD['sitegroup'] != 0) {
            $qb->add_constraint('sitegroup', '<>',0);
        }
        
        $skip_topics = array (
                'midcom.admin.aegir' => 0, 
                'midcom.admin.content' => 0, 
                'midcom.admin.content2' => 0,
                'midgard.admin.sitewizard' => 0,
                ); 
        // The QB does not yet support ordering on the PHP level
        //$qb->add_order("id desc");
        $result = $_MIDCOM->dbfactory->exec_query_builder($qb);
        
        foreach ($result as $key => $topic) 
        {
           
            $component = $topic->get_parameter('midcom', 'component');
            if ( ( $component != false ) && !array_key_exists($component, $skip_topics)) 
            {
                
                $this->_cache[$topic->id] = &$topic;
                $return[] = $topic->id;
            }
        }
        
        return $return;
    }
     
    
    /**
     * 
     * Get a node object either from the cache or brom the database
     * @param string object guid
     * @return the object
     */
    function & _get_node_object_from_cache($node)
    {
        if (array_key_exists($node, $this->_cache)) 
        {
            $obj = &$this->_cache[$node];
        } else 
        {
            $obj =$_MIDCOM->dbfactory->get_object_by_guid($node);
            $this->_cache[$node] = &$obj;
        }
        
        if (!is_object($obj)) 
        {
            $_MIDCOM->generate_error("Tried to get $node but didn't manage to get an object with this guid.");
        }
        return $obj;
    }
    
    /**
     * this lists the leaves of a componentnode.
     * @param mixed int or guid - id of the parent node.
     */
	function list_leaves ($node_up) 
    {
        if ($node_up =='0') 
        {
            return array();
        }
        $node = $this->get_node($node_up);       
        
        $context = &$this->get_context($node[MIDCOM_NAV_OBJECT]->id);
        
        $context->enter_context();
        // code indented to visualize different context        
            $leaves = $context->nav->list_leaves($node_up, true);
            
        $context->leave_context();

        if (!$leaves) 
        {
            return false;
        }
		return $leaves;
	}
    
    /**
     * get the context related to this node
     * @param int topicid.
     */
    function & get_context($topicid) 
    {
        if (!array_key_exists($topicid, $this->_contexts)) 
        {
            $this->generate_context($topicid);              
        } 
        
        return $this->_contexts[$topicid];
    } 
    
    /**
     * set up a new context that didn't exist before.
     * @param int topic id.
     */
    function generate_context($topicid) 
    {   
        $node =  new midcom_db_topic($topicid);
        $this->_contexts[$topicid] = new midcom_admin_content2_context();
        $this->_contexts[$topicid]->set_content_topic(&$node);
        $this->_contexts[$topicid]->set_admincontext();
        $this->_contexts[$topicid]->set_root_topic();
    }
    
    /**
     * internal helper. returns a topic object
     * from the cache or generates one and places
     * it in the cache.
     * @param int topic id to get.
     * @return object midcom_db_topic
     */
    function  & _get_topic_object($id) 
    {
        if (!array_key_exists($id, $this->_cache)  ) 
        {
            $topic =  new midcom_db_topic($id);
            if ($topic == null) 
            {
                $_MIDCOM->generate_error(__CLASS__. ":" . __FUNCTION__ ."Could not get topic with id $id!");
            }
            $this->_cache[$id] = & $topic;
        } 
        return  $this->_cache[$id];
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
        $leaf = &$this->get_leaf($leafid);
        if (!$leaf) 
        {
            return false;
        } 
        return $leaf[MIDCOM_NAV_OBJECT]->topic;
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
        if (!$node) 
        {
            return false;
        }
        if ($node[MIDCOM_NAV_OBJECT]->up == 0 ) 
        {
            // we have reached the root of the tree.
            return -1;
        }
        return $node[MIDCOM_NAV_OBJECT]->up;
    }
}
