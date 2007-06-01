<?php
/*
 * check and load needed classes.
 * */
//if (midcom_file_exists_incpath('HTML/TreeMenu.php')) {
    require_once('HTML/TreeMenu.php');
  
    if (!class_exists('HTML_TreeMenu')) {
        $class_loaded = false;
    } else {
        $class_loaded = true;
    }
//} else {
    //$class_loaded = false;
    /* torben: How can I add a nice errormessage to the logs so the user may see what is missing?
     * also is that prudent or will it just fill up the logs?
     $GLOBALS['midcom_debugger']->debug_add('You are missing the HTML_Treemenu PEAR package.
          Therefore you do not get the nice dynamic js-menutree.',MIDCOM_LOG_INFO);
          */

   
class midcom_admin_aegir_navigation_treemenu {

	/** 
	 * startprefix for menu
	 * @access private 
	 **/              
	var $_prefix;                                                                                                                                                                                                 

    /**
     * Pointer to the request_data array
     */
    var $_request_data = null;

    var $_menu_path = array();
    var $_menu_num  = 0;
    /**
     * Pointer to aegir_nav object
     * @access private
     */
    var $_nav = null;
    /**
     *  Pointer to view_contentmgr->viewdata
     *  @access private
     */
    var $_data = null;
    
    /**
     * Pointer to user sitegroup
     */
    var $_current_sg = 0;

    /**
     * string of html to be outputed before printMenu.
     * @access private
     */ 
    var $_html = "";

    /**
     * boolean , show leaves or only nodes?
     * @access private
     * @var boolean 
     */
    var $_show_leaves = true;
    
    /**
     * use the nodes MIDCOM_NAV_URL or MIDCOM_NAV_ID in %s of string.
     * @access private
     * @var boolean true = use MIDCOM_NAV_URL
     */
    var $_node_action_url = true;

    /**
     * Node action
     * used with sprintf to define the link to each node.
     * 
     * @access private
     * @var string action to add to href
     */
     var $_node_action = "";

    /**
     * use the leaves MIDCOM_NAV_URL or MIDCOM_NAV_ID in %s of string.
     * @access private
     * @var boolean false = use MIDCOM_NAV_URL
     */
    var $_leaf_action_url = true;

    /**
     * Leaf action
     * Used with sprintf to define the link to each node.
     * 
     * @access private
     * @var string action to add to href
     */
     var $_leaf_action = "";
    /**
     * The current users sitegroup
     */
     var $_sitegroup = null;
     
     /**
      * Max depth to go if this is not the subtree we're working on
      * Set to -1 for no max.
      * @var int maxdepth
      * @access private'
      */
     var $_max_depth = 4;
     /**
      * Total nr of nodes in menu
      */ 
     var $nr_nodes = 0;
     /**
      * Total nr of leaves in menu
      */
     var $nr_leaves = 0;
     /**
      * the icon of a closed folder
      */
     var $folder_icon         = 'folder.png';
     /**
      * the icon of an open folder
      */
     var $expanded_folder_icon = 'folder-expanded.png';
    /**
     *  Generate the object and set some globals.
     */
    function midcom_admin_aegir_navigation_treemenu() 
    {
        ////$data =& $_MIDCOM->get_custom_context_data('request_data');
        
        $this->_prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        
        $this->_leaf_action = $this->_prefix . "%s";
        $this->_node_action = $this->_prefix . "%s";
        
        
        $person = $_MIDCOM->auth->user->get_storage();
        $this->_sitegroup = $person->sitegroup;
        
    }
    
    /**
     * makeNodes recursive function  
     */
	function makeNodes(&$menu_path, $curr_level = 0,$expanded = true){
       
        $expanded = false;	 
	    $curr_level++;
        $curr_node = array_shift($menu_path);
        
        /*
        if ($curr_level == $this->_max_depth && count($menu_path) < 1) {
             return;
        }
        
        debug_add("Getting to level: $curr_level");
        */
        
	    if ($curr_node == -1 ) {
    
                                        
	    } else {
            debug_add("Getting to node $curr_node");    
		    $root_node = $this->_nav->get_node($curr_node);
            
            if ($this->_menu_num > 0 &&  $this->_menu_path[count($this->_menu_path)-1][MIDCOM_NAV_TYPE] == 'node' && 
                $this->_menu_path[count($this->_menu_path)-1][MIDCOM_NAV_ID] == $root_node[MIDCOM_NAV_ID] ) {
                    $expanded = true;
                    array_pop($this->_menu_path);
                    $this->_menu_num--;
            }
                
	        $node = new HTML_TreeNode(array('text' => $root_node[MIDCOM_NAV_NAME]   ,
                                        'link' => sprintf($this->_node_action,
                                                    ($this->_node_action_url)? 
                                                            $root_node[MIDCOM_NAV_URL] : $root_node[MIDCOM_NAV_ID] ) ,
	                                    'icon' => $this->folder_icon,
	                                    'expandedIcon' => $this->expanded_folder_icon,
	                                    'expanded' => $expanded
	                                    ));
	    }
        $nodes = array();
	    if ($curr_node != '' ) {
	 	 $nodes = $this->_nav->list_nodes($curr_node);
        } 
        debug_add("Listing nodes for: $curr_node");
	 	if (count($nodes) > 0) {
            
			foreach ($nodes as $counter => $nodeid ) {
                
                $this->nr_nodes++;
				if (count($menu_path)>0 && $nodeid == $menu_path[0]) 
                {
                    $menu_node = &call_user_func(array(&$this,'makeNodes'), &$menu_path,$curr_level,true);
				} else {
                    
                    debug_add("Adding subnodes for  $nodeid");
                    if ($curr_level < $this->_max_depth ) {
                        $nodearray = array ( 0 => $nodeid);
                        $menu_node = &call_user_func(array(&$this,'makeNodes'),&$nodearray,$curr_level,false);
                    } else {
                        debug_add("Reached cutoff epth at $curr_level");
                        if ($this->_menu_num > 0 &&  $this->_menu_path[count($this->_menu_path)-1][MIDCOM_NAV_TYPE] == 'node' && 
                            $this->_menu_path[count($this->_menu_path)-1][MIDCOM_NAV_ID] == $nodeid ) {
                                $expanded = true;
                                array_pop($this->_menu_path);
                                $this->_menu_num--; 
                        }
                        
                        $subnode = $this->_nav->get_node($nodeid);
                        
  			            $menu_node = & new HTML_TreeNode( 
                        array(  'text' => $subnode[MIDCOM_NAV_NAME], 
					 	        'link' => sprintf($this->_node_action,
                                                    ($this->_node_action_url)? 
                                                            $subnode[MIDCOM_NAV_URL] : $subnode[MIDCOM_NAV_ID] ) ,
                                'icon' => $this->folder_icon,
                                'expandedIcon' => $this->expanded_folder_icon,
								'expanded' => $expanded
						));
                    }
                }
				
                $node->addItem($menu_node);
            }
	 	}
        
		if ($curr_node > 0 && $this->_show_leaves) {	    	
			$leaves = $this->_nav->list_leaves($curr_node, true);
			if (is_array($leaves) && count($leaves) > 0) 
	        {
                debug_add("listing leaves for node $curr_node,. total: " . count($leaves) );
	            foreach ($leaves as $order => $leafid) {
                    
                    if ($this->_menu_num > 0 && $this->_menu_path[count($this->_menu_path)-1][MIDCOM_NAV_TYPE] == 'leaf' && 
                       $this->_menu_path[count($this->_menu_path)-1][MIDCOM_NAV_ID] == $root_node[MIDCOM_NAV_ID] ) {
                        $expanded = true;
                        array_pop($this->_menu_path);
                        $this->_menu_num--;
                    }
                    
				    $leaf	= $this->_nav->get_leaf($leafid);
	                $icon = 'jonah.gif';
                    
	                if ($leaf[MIDCOM_NAV_SITE][MIDCOM_NAV_URL] == '.html') 
	                {
	                  $icon = 'jonah-nocontent.gif';
	                }
                    
					$item_node = &new HTML_TreeNode(array(
										'text' => $leaf[MIDCOM_NAV_NAME] , 
										'link' => sprintf($this->_leaf_action, ($this->_leaf_action_url)? $leaf[MIDCOM_NAV_URL] : $leaf[MIDCOM_NAV_ID] ) , 
										'icon' => $icon, 
                                        'expandedIcon' => $icon));
					$this->nr_leaves++;
                    $node->addItem($item_node);
	            }
			}
		}
        
        return $node;
	}

    
    /**
     * set _leaves to false. Use this if you do not want any leaves in the tree.
     * @access public
     * @return void
     * @param boolean yes - wther to show the leaves or not.
     */
    function show_leaves($yes) {
        $this->_show_leaves = $yes;
    }
    
    /**
     * Use this to define the link action if the normal one doesn't suit you
     * 
     * The default is to expand the href of the node to $prefix . $node[MIDCOM_NAV_URL]
     * 
     * @return void
     * @param string action a string that will be expanded using sprintf (string)
     * @param boolean url_or_id defines if the %s part of the string should expand to the node id or
     *  the nodes MIDCOM_NAV_URL
     */
    function set_node_action($action, $url_or_id) {
        $this->_node_action = $action;
        $this->_node_action_url = $url_or_id;
        
    }
    
    /**
     * Use this to define the leaf link action if the normal one doesn't suit you.
     * 
     * The default is to expand the href of the leaf to $prefix . $node[MIDCOM_NAV_URL]
     * 
     * @return void
     * @param string action a string that will be expanded using sprintf (string)
     * @param boolean url_or_id defines if the %s part of the string should expand to the leaf id or
     *  the leaf MIDCOM_NAV_URL
     */
    function set_leaf_action($action, $url_or_id) {
        $this->_leaf_action = $action;
        $this->_leaf_action_url = $url_or_id;
        
    }
    
    
    
    /**
     * Print out the tree
     * outputs the whole shebang.
     */
    function to_html() 
    {
        $this->_html .= '<script src="'.MIDCOM_STATIC_URL . '/midcom.admin.content/TreeMenu.js" language="JavaScript" type="text/javascript"></script>';
    
        //$this->_request_data =& $_MIDCOM->get_custom_context_data('request_data');
        $this->_nav =  &$this->_request_data['aegir_interface']->get_navigation();
    	

        
	    $menu = new HTML_TreeMenu();
        
        $this->_menu_path = $this->_nav->get_breadcrumb_array();
        $this->_menu_num  = count($this->_menu_path);
        $runtime = 0;
        foreach ($this->_request_data['aegir_interface']->registry as $key => $value) {
            //$prefix = $this->_prefix;
            
            
            if ($key === $this->_request_data['aegir_interface']->current ) {
                /*breadcrumb array needs help from above to do something usefull, but then it's
                 * ok :)' */
                        $this->nr_nodes++;
            
                
                
                $node = new HTML_TreeNode(array('text' => $this->_request_data['aegir_interface']->registry[$this->_request_data['aegir_interface']->current]['name'],
                                        'link' => sprintf($this->_node_action,
                                                    ($this->_node_action_url)? 
                                                            "" : 0 ) ,
                                        'icon' => $this->folder_icon,
                                        'expandedIcon' => $this->expanded_folder_icon,
                                        'expanded' => true
                                        ));
                $subnodes = $this->_nav->list_nodes($this->_nav->get_root_node());
                foreach ($subnodes as $key => $node_id) {
                                                            
                    $sub = &call_user_func(array (&$this, 'makeNodes'),array(0=> $node_id));
                    $node->addItem(&$sub);
                }
                $menu->addItem(&$node);
            } elseif( ! (bool ) $value['hide'] ) {
                $this->nr_nodes++;
                $lnode =& new HTML_TreeNode(array('text' => $value['name']    ,
                                        'link' => $this->_prefix . $key,
                                        'icon' => (array_key_exists('icon', $value)) ? $value['icon'] : 'folder.png' ,
                                        'expandedIcon' => (array_key_exists('icon', $value)) ? $value['icon'] : 'folder.png' ,
                                        'expanded' =>  false
                                        ));
                $menu->addItem(&$lnode);
            }
            //$this->_prefix = $prefix;
        }

	    $treeMenu = &new HTML_TreeMenu_DHTML($menu, array('images' => MIDCOM_STATIC_URL. '/stock-icons/16x16/', 'defaultClass' => 'treeMenuDefault'));
        echo $this->_html;
	    $treeMenu->printMenu();
        echo "<p>Nr of nodes: " .  $this->nr_nodes . " <br />Nr of leaves " . $this->nr_leaves . "</p>";
        echo "<p>Runtime: $runtime</p>";
    }
}

?>