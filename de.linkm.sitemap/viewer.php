<?php
/**
 * @package de.linkm.sitemap
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Sitemap MidCOM Viewer class.
 * 
 * @package de.linkm.sitemap
 */

class de_linkm_sitemap_viewer {

    var $_debug_prefix;

    var $_config;         // midcom_helper_configuration instance
    var $_nav;            // midcom_helper_nav reference
    var $_root_node_id;   // int
    var $_current_node;   // int
    var $_topic;
    var $_prefix;
    
    var $errcode;
    var $errstr;
    

    // == OFFICIAL INTERFACE TO THE COMPONENT CONCEPT CLASS ====================

    // constructor de_linkm_sitemap_viewer($object, $config)
    //
    // Initialize and customize the sitemap builder using the current $config
    // and the URL hook topic given in $object (currently ignored).
    //
    function de_linkm_sitemap_viewer($object, $config) {
        $this->_debug_prefix = "de_linkm_sitemap_viewer::";
        
        $this->_config = $config;
        $this->_root_node_id = null;
        $this->_current_node = null;
        $this->_topic = $object;
        $this->_prefix = "";
        
        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";
    }

    // can_handle($argc, $argv)
    //
    // Check whether the current request is valid and can be handled.
    //
    function can_handle($argc, $argv) {
        debug_push($this->_debug_prefix . "can_handle()");

        // No configuration-dependent checks necessary. ???

        // Nothing else to be done -- with a valid config we're generally able
        // to handle *any* request :-)

        debug_pop();
        return true;
    }

    // handle()
    //
    // Initialize the sitemap access to midcom_helper__basicnav.
    // 
    function handle() {
        debug_push($this->_debug_prefix . "handle()");
        
        if ($this->errcode != MIDCOM_ERROK) {
            debug_add("Called with errcode != OK");
            debug_pop();
            return false;
        }
        
        // Get an instance of the navigation access class
        $this->_nav = new midcom_helper_nav(); // contextid=0: main context
        if (! $this->_nav) {
            debug_add("Unable to init midcom_helper_nav w/ context 0");
            debug_pop();
            return false;
        }
        
        $GLOBALS["midcom"]->set_pagetitle($this->_topic->extra);
        $mgd = $GLOBALS["midcom"]->get_midgard();
        $this->_prefix = $mgd->self;
        
        $root = $this->_config->get("root_topic");
        if ($root != null && $root != "") {
            $topic = mgd_get_object_by_guid($root);
            if (!$topic)
                die ("Could not open root topic with GUID [$root]; please check your component configuration: " . mgd_errstr());
            if (! mgd_is_in_topic_tree($this->_nav->get_root_node(), $topic->id))
                die ("The topic with GUID [$root] is not within the content tree as indicated by NAP. Check your configuration.");
            $this->_root_node_id = $topic->id;
            $prefix = "";
            $nodeid = $this->_root_node_id;
            while ($nodeid != -1 && $nodeid != $this->_nav->get_root_node()) {
                $node = $this->_nav->get_node($nodeid);
                $prefix = $node[MIDCOM_NAV_URL] . $prefix;
                $nodeid = $this->_nav->get_node_uplink($nodeid);
            }
            $this->_prefix .= $prefix;
        } else {
            $this->_root_node_id = $this->_nav->get_root_node();
        }
        
        debug_pop();
        return true;
    }
    
    // show()
    //
    // Display sitemap.
    // 
    function show() {
        global $view_meta;
        
        debug_push($this->_debug_prefix . "show");
        
        $view_meta["prefix"] = $this->_prefix;
        $view_meta["depth"] = 0;
        global $view_title;
        $view_title = $this->_topic->extra;

        midcom_show_style("begin-sitemap");
        midcom_show_style("enter-level");
        
        if ($this->_config->get("display_root")) 
        {
            if (!$this->_show_node($this->_root_node_id))
            {
                die("An error occured: de_linkm_sitemap_viewer::_show_node($this->_root_node_id) returned false. Aborting.");
            }
            
        } 
        else
        {
            $subnodes = $this->_nav->list_nodes($this->_root_node_id);
            if ($subnodes === false)
            {
                die("An error occured: de_linkm_sitemap_viewer::show: Could not list root's subnodes");
            }
            
            foreach ($subnodes as $id)
            {
                if (!$this->_show_node($id))
                {
                    die("An error occured: de_linkm_sitemap_viewer::_show_node($id) returned false. Aborting.");
                }
            }
            
        }
        
        midcom_show_style("leave-level");
        midcom_show_style("end-sitemap");
        
        
        debug_pop();
        return true;
    }

    // get_metadata()
    //
    // Return MidCOM meta data array about the sitemap.
    // 
    function get_metadata() {

        return array (
            MIDCOM_META_CREATOR => 0,
            MIDCOM_META_EDITOR  => 0,
            MIDCOM_META_CREATED => time(),
            MIDCOM_META_EDITED  => time()
        );
    }


    // == PRIVATE FUNCTIONS ====================================================

    function _show_node($nodeid) 
    {
        global $view;
        global $view_meta;
        
        // Load the node
        $previous = $this->_current_node;
        $this->_current_node = $this->_nav->get_node($nodeid);
        
        // Start a new node and display it
        $view = $this->_current_node;
        midcom_show_style("node-start");
        midcom_show_style("node");
        
        // Try to load Child elements        
        $subnodes = $this->_nav->list_nodes($nodeid);
        $leaves = null;
        if ($subnodes === false) 
        {
            midcom_show_style("node-end");
            return false;
        }
        if ($this->_config->get("hide_leaves") == false)
        {
            $leaves = $this->_nav->list_leaves($nodeid);
            if ($leaves === false) 
            {
                midcom_show_style("node-end");
                return false;
            }
        }
        
        // Now display all subnodes and the leaves in the right order
        if ($this->_config->get("leaves_first")) 
        {
            if (! $this->_show_leaves($leaves)) 
            {
                midcom_show_style("node-end");
                return false;
            }
                
            if (! $this->_show_subnodes($subnodes)) 
            {
                midcom_show_style("node-end");
                return false;
            }
        } 
        else 
        {
            if (! $this->_show_subnodes($subnodes)) 
            {
                midcom_show_style("node-end");
                return false;
            }
            
            if (! $this->_show_leaves($leaves)) 
            {
                midcom_show_style("node-end");
                return false;
            }
        }
        
        // Close current node
        midcom_show_style("node-end");
        
        // Clean up
        $this->_current_node = $previous;
        
        return true;
    }
    
    function _show_leaves($leaves) 
    {
        global $view;
        global $view_meta;
        
        if (is_null($leaves))
        {
            // hide_leaves seems to be set.
            return true;
        }
        
        if (count($leaves) > 0) 
        {
            $old_prefix = $view_meta["prefix"];
            $view_meta["prefix"] .= $this->_current_node[MIDCOM_NAV_URL];
            
            // Begin leaves listing
            midcom_show_style("begin-leaves");
            
            // Iterate over the leaves and display them
            foreach ($leaves as $id) 
            {
                $view = $this->_nav->get_leaf($id);
                if ($this->_config->get("hide_index_articles") && ($view[MIDCOM_NAV_URL] == '') ) continue;
               
                midcom_show_style("leaf");
            }
            
            // End leaves listing
            midcom_show_style("end-leaves");
            $view_meta["prefix"] = $old_prefix;        
        }
        
        return true;
    }
    
    function _show_subnodes($subnodes) 
    {
        global $view;
        global $view_meta;
        
        if (count($subnodes) > 0) 
        {
            // First we have to decend a level for the subnode-listing 
            $view_meta["depth"]++;
            $old_prefix = $view_meta["prefix"];
            $view_meta["prefix"] .= $this->_current_node[MIDCOM_NAV_URL];
            
            midcom_show_style("enter-level");
            
            // Iterate over the nodes and display them
            foreach ($subnodes as $id) 
            {
                if (!$this->_show_node($id)) 
                {
                    $view_meta["depth"]--;
                    midcom_show_style("leave-level");
                    return false;
                }
            }
            
            // Finally we have to ascend back up to the previous level
            midcom_show_style("leave-level");
            
            $view_meta["depth"]--;
            $view_meta["prefix"] = $old_prefix;
        }
        
        return true;
    }
} // viewer

?>
