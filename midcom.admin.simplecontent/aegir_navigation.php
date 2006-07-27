<?php
/**
 * Created on Aug 3, 2005
 * @package midcom.admin.simplecontent
 */

class midcom_admin_simplecontent_aegir_navigation extends midcom_admin_aegir_module_navigation  {


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
		if (!is_object($id)) {
		
			$topic = new midcom_baseclasses_database_topic($id);
		} else {
			$topic = &$id;
		}
        
        if (! $topic)
        {
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                "Cannot load NAP information, aborting: Could not load the topic {$id} from the database (" 
                . mgd_errstr() . ').');
            // This will exit().
        }
        
        //debug_add("Trying to load NAP data for topic {$topic->name} (#{$topic->id})");		


        // until this is fixed in midcom_services_auth
        if ($_MIDGARD['sitegroup'] != $topic->sitegroup) { 
            $perms = 0;
        }
        
        $nodedata[MIDCOM_NAV_NAME] = $topic->name;
        $nodedata[MIDCOM_NAV_URL] = "topic/". $topic->id . '.html';
        $nodedata[MIDCOM_NAV_ADMIN][MIDCOM_NAV_URL] = "topic/". $topic->id . '.html';
        $nodedata[MIDCOM_NAV_ADMIN][MIDCOM_NAV_NAME] = $topic->name;
        $nodedata[MIDCOM_NAV_NAME] = trim($nodedata[MIDCOM_NAV_NAME]) == '' ? $topic->name : $nodedata[MIDCOM_NAV_NAME];
        $nodedata[MIDCOM_NAV_GUID] = $topic->guid();
        $nodedata[MIDCOM_NAV_ID] = $topic->id;
        $nodedata[MIDCOM_NAV_TYPE] = 'node';
        $nodedata[MIDCOM_NAV_ICON] = null;
        $nodedata[MIDCOM_NAV_SCORE] = $topic->score;
        $nodedata[MIDCOM_NAV_OBJECT]  = &$topic;
        $nodedata[MIDCOM_NAV_COMPONENT] = 'simplecontent';
        
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
	function get_leaf($leaf_id) {
		debug_push_class(__CLASS__, __FUNCTION__);
		$article = new midcom_baseclasses_database_article($leaf_id);
		
		if (!$article) {
			debug_pop();
			 return false;
		}
        
		  $toolbar = array();
        $ret = array (
                	MIDCOM_NAV_URL => "article/{$article->id}.html",
                    MIDCOM_NAV_ID => $article->id,
                	MIDCOM_NAV_NAME => ($article->title != '') ? $article->title : $article->name,
                    MIDCOM_NAV_SITE => Array 
                    (
                        MIDCOM_NAV_URL => "{$article->name}.html",
                        MIDCOM_NAV_NAME => ($article->title != '') ? $article->title : $article->name
                    ),
                    MIDCOM_NAV_ADMIN => Array 
                    (
                        MIDCOM_NAV_URL => "article/{$article->id}.html",
                        MIDCOM_NAV_NAME => ($article->title != '') ? $article->title : $article->name
                    ),
                    MIDCOM_NAV_GUID => $article->guid,
                    MIDCOM_NAV_TOOLBAR => $toolbar,
                    MIDCOM_NAV_TYPE => 'leaf',
                    MIDCOM_NAV_COMPONENT => 'simplecontent',
                    MIDCOM_NAV_ICON => 'midcom.admin.aegir/document.png',
                    /* MIDCOM_META_CREATOR => $article->creator,
                    MIDCOM_META_EDITOR => $article->revisor,
                    MIDCOM_META_CREATED => $article->created,
                    MIDCOM_META_EDITED => $article->revised,
                    */
                    MIDCOM_NAV_OBJECT => &$article,
                    MIDCOM_NAV_ICON => null,
                );
		
		debug_pop();
        return $ret;
	}
	/**
     *. 
     */
	function list_nodes($node_up = 0) {
		debug_push_class(__CLASS__, __FUNCTION__);
		$qb = $_MIDCOM->dbfactory->new_query_builder('midcom_baseclasses_database_topic');
		if (!is_int($node_up) && $node_up != '0' ) {
            return array();
        }
		$qb->add_constraint('up', '=',$node_up);
        //TODO: there should be a better way
        
		//$qb->add_constraint('sitegroup', '=', $_MIDCOM->auth->user->sitegroup);
		$result = $qb->execute();
        
		$nodes = array();        
		for ($i = 0 ; $i < count($result); $i++) {
			$nodes[$i] = $result[$i]->id;
		}
		debug_pop();
		return $nodes;
			
	}
	 /**
     * Leaf listing function, the default implementation returns an empty array indicating
     * no leaves. Note, that the active leaf index set by the other parts of the component
     * must match one leav out of this list.
     * 
     * Here are some code fragments, that you usually connect through some kind of 
     * while $articles->fetch() loop:
     * 
     * <code>
     * <?php
     *  // Prepare the toolbar
     *  $toolbar[50] = Array(
     *      MIDCOM_TOOLBAR_URL => '',
     *      MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
     *      MIDCOM_TOOLBAR_HELPTEXT => null,
     *      MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
     *      MIDCOM_TOOLBAR_ENABLED => true
     *  );
     *  $toolbar[51] = Array(
     *      MIDCOM_TOOLBAR_URL => '',
     *      MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
     *      MIDCOM_TOOLBAR_HELPTEXT => null,
     *      MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
     *      MIDCOM_TOOLBAR_ENABLED => true
     *  );
     *  
     *  while ($articles->fetch ()) {
     *      // Match the toolbar to the correct URL.
     *      $toolbar[50][MIDCOM_TOOLBAR_URL] = "edit/{$articles->id}.html";
     *      $toolbar[51][MIDCOM_TOOLBAR_URL] = "delete/{$articles->id}.html";
     *      
     *      $leaves[$articles->id] = array 
     *      (
     *          MIDCOM_NAV_SITE => Array 
     *          (
     *              MIDCOM_NAV_URL => $articles->name . ".html",
     *              MIDCOM_NAV_NAME => ($articles->title != "") ? $articles->title : $articles->name
     *          ),
     *          MIDCOM_NAV_ADMIN => Array 
     *          (
     *              MIDCOM_NAV_URL => "view/" . $articles->id,
     *              MIDCOM_NAV_NAME => ($articles->title != "") ? $articles->title : $articles->name
     *          ),
     *          MIDCOM_NAV_GUID => $articles->guid(),
     *          MIDCOM_NAV_TOOLBAR => $toolbar,
     *          MIDCOM_META_CREATOR => $articles->creator,
     *          MIDCOM_META_EDITOR => $articles->revisor,
     *          MIDCOM_META_CREATED => $articles->created,
     *          MIDCOM_META_EDITED => $articles->revised
     *      )
     *  }
     *  
     *  return $leaves;
     *  
     * ?>
     * </code>
     * 
     * @return Array NAP compilant list of leaves. 
     */
    /**
     * Returns all leaves for the current content topic.
     * 
     * It will hide the index leaf from the NAP information unless we are in Autoindex
     * mode. The leaves' title are used as a description within NAP, and the toolbar will
     * contain edit and delete links.
     */
    function get_leaves($node_up = 0) 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $sort = 'score';
        $reverse = false;
        if (! $sort) 
        {
            $sort = 'score';
        }
        if (substr($sort, 0, 7) == 'reverse')
        {
            $sort = substr($sort, 8);
            $reverse = true;
        }
        if ($node_up == 0) return array();
        $qb = $_MIDCOM->dbfactory->new_query_builder('midcom_baseclasses_database_article');
        $qb->add_constraint('topic', '=', $node_up);
        // The QB does not yet support ordering on the PHP level
        // $qb->add_order($sort);
        $result = $_MIDCOM->dbfactory->exec_query_builder($qb);
        
        if ($result === false)
        {
            debug_add('Failed to query the content topic, returning no leaves, see debug level log for details.', MIDCOM_LOG_INFO);
            debug_print_r('Content Topic was:', $this->_content_topic);
            debug_pop();
            return Array();
        }
        
        // Prepare everything
        $leaves = array ();
        $toolbar[50] = Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            MIDCOM_TOOLBAR_ENABLED => true
        );
        $toolbar[51] = Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ENABLED => true
        );

        foreach ($result as $article)
        {
            // Match the toolbar to the correct URL.
            $toolbar[50][MIDCOM_TOOLBAR_URL] = "simplecontent/article/edit/{$article->id}.html";
            $toolbar[50][MIDCOM_TOOLBAR_HIDDEN] = ($_MIDCOM->auth->can_do('midgard:update', $article) == false);
            $toolbar[51][MIDCOM_TOOLBAR_URL] = "simplecontent/article/delete/{$article->id}.html";
            $toolbar[51][MIDCOM_TOOLBAR_HIDDEN] = ($_MIDCOM->auth->can_do('midgard:delete', $article) == false);
            
            $leaves[$article->id] = array 
            (
                MIDCOM_NAV_SITE => Array 
                (
                    MIDCOM_NAV_URL => "simplecontent/article/{$article->name}.html",
                    MIDCOM_NAV_NAME => ($article->title != '') ? $article->title : $article->name
                ),
                MIDCOM_NAV_URL => "simplecontent/article/{$article->name}.html",
                MIDCOM_NAV_NAME => ($article->title != '') ? $article->title : $article->name,
                MIDCOM_NAV_ADMIN => Array 
                (
                    MIDCOM_NAV_URL => "simplecontent/article/view/{$article->id}",
                    MIDCOM_NAV_NAME => ($article->title != '') ? $article->title : $article->name
                ),
                MIDCOM_NAV_GUID => $article->guid,
                MIDCOM_NAV_TOOLBAR => $toolbar,
                MIDCOM_NAV_TYPE => 'leaf',
                MIDCOM_META_CREATOR => $article->creator,
                MIDCOM_META_EDITOR => $article->revisor,
                MIDCOM_META_CREATED => $article->created,
                MIDCOM_META_EDITED => $article->revised
            );
            
        }
        
        debug_pop();
        return $leaves;
    }
     
     
	function list_leaves ($node_up) {
		debug_push_class(__CLASS__, __FUNCTION__);
		if (!is_int($node_up)) {
			return array();
		}
		
		$qb = $_MIDCOM->dbfactory->new_query_builder('midcom_baseclasses_database_article');
        $qb->add_constraint('topic', '=', $node_up);
        // The QB does not yet support ordering on the PHP level
        // $qb->add_order($sort);
        $result = $_MIDCOM->dbfactory->exec_query_builder($qb);
        
        if (is_null($result))
        {
            debug_add('Failed to query the content topic, returning no leaves, see debug level log for details.', MIDCOM_LOG_INFO);
            debug_print_r('Content Topic was:', $this->_content_topic);
            debug_pop();
            return Array();
        } 
        
        // Prepare everything
        $leaves = array ();
		$i = 0;
        
		foreach ($result as $article) {
			$leaves[$i++] = $article->id;
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
    function get_leaf_uplink($leafid) {
        
        $leaf =  $this->get_leaf($leafid);
        if (!$leaf) {
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
        if (!$node) {
            return false;
        }
        if ($node[MIDCOM_NAV_OBJECT]->up == 0 ) {
            // we have reached the root of the tree.
            return -1;
        }
        return $node[MIDCOM_NAV_OBJECT]->up;
    }
}
?>
