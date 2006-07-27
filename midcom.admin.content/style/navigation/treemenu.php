<?php
/*
 * check and load needed classes.
 * */
if (midcom_file_exists_incpath('HTML/TreeMenu.php')) {
    require_once('HTML/TreeMenu.php');
  
    if (!class_exists('HTML_TreeMenu')) {
        $class_loaded = false;
    } else {
        $class_loaded = true;
    }
} else {
    $class_loaded = false;
    /* torben: How can I add a nice errormessage to the logs so the user may see what is missing?
     * also is that prudent or will it just fill up the logs?
     $GLOBALS['midcom_debugger']->debug_add('You are missing the HTML_Treemenu PEAR package.
          Therefore you do not get the nice dynamic js-menutree.',MIDCOM_LOG_INFO);
          */
}
   
class midcom_admin_content_navigation_treemenu {
    /**
     * pointer to midcom object
     * @var object
     * @access private
     * */
    var $_midcom = null;
                                                                                                                                                                                                               
    /**
     * pointer to
     * view_contentmgr object
     * @var object
     * @access private
     * */
    var $view_contentmgr = null;

    var $_menu_path = array();
    /**
     * Pointer to midcom_helper_nav
     * @access private
     */
    var $_nav = null;
    /**
     *  Pointer to view_contentmgr->viewdata
     *  @access private
     */
    var $_data = null;

    /**
     * string of html to be outputed before printMenu.
     * @access private
     */ 
    var $_html = "";

		function makeNodes(&$menu_path, $max_levels,$curr_level = 0,$expanded = true){
            $icon         = 'folder.png';
            $expandedIcon = 'folder-expanded.png';
            /* grr, I wish I didn't have to pass this by reference...  */
		    $curr_node = array_shift($menu_path);
            $root_node = $this->_nav->get_node($curr_node);
            $node = new HTML_TreeNode(array('text' => $root_node[MIDCOM_NAV_NAME] ,
                                        'link' => $this->_data['adminprefix'] . $root_node[MIDCOM_NAV_ID] . '/data',
                                        'icon' => $icon,
                                        'expandedIcon' => $expandedIcon,
                                        'expanded' => $expanded
                                        ));
        
		 	$nodes = $this->_nav->list_nodes($curr_node, true);
			if (is_array($nodes) && count ($nodes) > 0 ) {
				foreach ($nodes as $counter => $nodeid ) {
          
					if (count($menu_path)>0 && $nodeid == $menu_path[0]) {
                        $menu_node = &call_user_func(array(&$this,'makeNodes'), &$menu_path,$max_levels,$curr_level,true);
					} else {
            
                        if ($curr_level < $max_levels) {
                            $nodearray = array ( 0 => $nodeid);
                            $menu_node = &call_user_func(array(&$this,'makeNodes'),&$nodearray,$max_levels,$curr_level,false);
                        } else {
                            $subnode = $this->_nav->get_node($nodeid);
	  			            $menu_node = & new HTML_TreeNode( 
                            array(  'text' => $subnode[MIDCOM_NAV_NAME] , 
						 	        'link' => $this->_data['adminprefix'] . $subnode[MIDCOM_NAV_ID]. '/data/' ,
									'icon' => $icon, 
									'expandedIcon' => $expandedIcon, 
									'expanded' => $expanded
							)); 
                        }
                    }
					
                    $node->addItem($menu_node);
                }
		    }	
			$leaves = $this->_nav->list_leaves($curr_node, true);
			if (is_array($leaves) && count($leaves) > 0) 
            {
                foreach ($leaves as $order => $leafid) {
				    $leaf	= $this->_nav->get_leaf($leafid);
                    $icon = 'jonah.gif';
                    if ($leaf[MIDCOM_NAV_SITE][MIDCOM_NAV_URL] == '.html') 
                    {
                      $icon = 'jonah-nocontent.gif';
                    }
					$item_node = &new HTML_TreeNode(array(
										'text' => $leaf[MIDCOM_NAV_NAME] , 
										'link' => $this->_data['adminprefix']. $curr_node . '/data/' .$leaf[MIDCOM_NAV_URL] , 
										'icon' => $icon, 'expandedIcon' => $expandedIcon));
    				$node->addItem($item_node);
                }
			}
        
            $curr_level++;
            return $node;
		}

    /**
     *  Generate the object and set some globals.
     */
    function midcom_admin_content_navigation_treemenu() 
    {
        global $view_contentmgr;
        $this->_midcom =& $_MIDGARD;
        $this->_view_contentmgr = &$view_contentmgr;
        $this->_data = & $this->_view_contentmgr->viewdata; 
        $this->_nav =  new midcom_helper_nav($this->_data["context"]);
    }
    
    function to_html () 
    {
        $this->_html .= '<script src="'.MIDCOM_STATIC_URL . '/midcom.admin.content/TreeMenu.js" language="JavaScript" type="text/javascript"></script>';
    
    
        $datamode = $this->_data["adminmode"] == "data" ? true : false;
	      $menu_array = array();
        $prefix = $GLOBALS['midcom']->get_context_data($this->_data["context"],MIDCOM_CONTEXT_ANCHORPREFIX);
        $curr_node = $this->_nav->get_current_node();
        $curr_leaf = $this->_nav->get_current_leaf();
        $nodeid = $curr_node;
	    $node_path = array();
    	// create nodelist
	    for ($i = 0; $nodeid > 0; $i++) 
        {
			    $node_path[$i] = $nodeid; 
			    $nodeid = $this->_nav->get_node_uplink($nodeid);
			    if (!$nodeid)
			    {
			         break;
			    }
	    }
        // ugly but makes life easier:
        $menu_path = array_reverse($node_path);
        $i = 0;
        $node_path = array();
        /* array_reverse reverses the array, now we fix the array keys.  */
        foreach ($menu_path as $k => $v ) 
        {
          $node_path[$i] = $v;
          $i++;
        }
    
    
	    $menu = new HTML_TreeMenu();
        $item = call_user_func(array (&$this, 'makeNodes'),&$node_path,5);
  	    $menu->addItem($item);
	    $treeMenu = &new HTML_TreeMenu_DHTML($menu, array('images' => MIDCOM_STATIC_URL. '/stock-icons/16x16/', 'defaultClass' => 'treeMenuDefault'));
        echo $this->_html;
	    $treeMenu->printMenu();
    }
}
?>
