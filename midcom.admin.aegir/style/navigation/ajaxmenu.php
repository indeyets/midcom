<?php

/**
 * Created on Sep 23, 2005
 * @author tarjei huse
 * @package midcom.admin.aegir
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */

class midcom_admin_aegir_navigation_ajaxmenu
{

    /** 
     * startprefix for menu
     * @access private 
     **/
    var $_prefix;

    /**
     * Pointer to the request_data array
     */
    var $_request_data = null;

    var $_menu_path = array ();
    var $_menu_num = 0;
    /**
     * Pointer to aegir_nav object
     * @access private
     */
    var $_nav = null;

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
     * Max depth to go if this is not the subtree we're working on
     * Set to -1 for no max.
     * @var int maxdepth
     * @access private'
     */
    var $maxlevel = 1;
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
    var $folder_icon = 'folder.png';
    /**
     * the icon of an open folder
     */
    var $expanded_folder_icon = 'folder-expanded.png';

    /**
     *  Generate the object and set some globals.
     */
    function midcom_admin_aegir_navigation_treemenu()
    {
        //$request_data =& $_MIDCOM->get_custom_context_data('request_data');

        $this->_leaf_action = $this->_prefix."%s";
        $this->_node_action = $this->_prefix."%s";

        

    }
    
    function set_up() 
    {
        $person = $_MIDCOM->auth->user->get_storage();
        $this->_sitegroup = $person->sitegroup;
        $this->_request_data = & $_MIDCOM->get_custom_context_data('request_data');
        $this->_prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_nav = & $this->_request_data['aegir_interface']->get_navigation();
    }

    /**
     * recursive function to generate the unordered list.
     * @param string $rootid rootstring to pass on to list_items();
     * @param int $level current level of recursion
     * @param array  $currpath used to define if a certain path should be open.
     * @param boolean onlysub if only the rootid node should be added as an element.   
     * @return string html unordered list.
     * */
    function create_menu_nodes($rootid, $level, $currpath = array (), $onlysub = false)
    {
        debug_push_class(__CLASS__,__FUNCTION__);
        $obj = false;
        $nodes = false;
        $leaves = false;
        $level ++;
        $html = "";
        $style = "";
        if ($rootid == '0')
        {
            $root = (string) $rootid;
        }
        else
        {
            $obj = $this->_nav->get_node($rootid);

            if ($obj && array_key_exists(MIDCOM_NAV_ICON, $obj))
            {
                $style = "style=\"list-style-image:url(".MIDCOM_STATIC_URL.$obj[MIDCOM_NAV_ICON].");\"";
            }

            $root = $obj[MIDCOM_NAV_ID];
            
        }
        
        if ($this->maxlevel < $level && !array_key_exists($obj[MIDCOM_NAV_ID], $this->_menu_path))
        {
            debug_pop();
            return $html.= $this->add_closed_node($obj[MIDCOM_NAV_ID], 
                                                  $obj[MIDCOM_NAV_NAME], 
                                                  $obj[MIDCOM_NAV_URL], 
                                                  $obj[MIDCOM_NAV_COMPONENT] , 
                                                  $obj[MIDCOM_NAV_ICON]);
        }
        
        $nodes = $this->_nav->list_nodes($root);
        $leaves = $this->_nav->list_leaves($root);

        
        

        if (count($leaves) > 0 || count($nodes) > 0)
        {
            /* add the root object. */
            if ($root !== '0' && !$onlysub)
            {

                if ( !array_key_exists($obj[MIDCOM_NAV_ID], $this->_menu_path))
                {
                    $html .= $this->add_closed_node_with_content ($obj[MIDCOM_NAV_ID], 
                                                  $obj[MIDCOM_NAV_NAME], 
                                                  $obj[MIDCOM_NAV_URL], 
                                                  $obj[MIDCOM_NAV_COMPONENT], 
                                                  $obj[MIDCOM_NAV_ICON]);
                }
                elseif (array_key_exists($obj[MIDCOM_NAV_ID], $this->_menu_path))
                {
                    
                    $html .= $this->add_open_node($obj[MIDCOM_NAV_ID], 
                                                  $obj[MIDCOM_NAV_NAME], 
                                                  $obj[MIDCOM_NAV_URL], 
                                                  $obj[MIDCOM_NAV_COMPONENT], 
                                                  $obj[MIDCOM_NAV_ICON]);
                }
            }
            $inner = "";
            
            if ($nodes)
            {
                foreach ($nodes as $key => $nodeid)
                {
                    if ( ! array_key_exists($nodeid, $this->_menu_path))
                    {
                        $node = $this->_nav->get_node($nodeid);
                        
                        $inner .= $this->add_closed_node ($node[MIDCOM_NAV_ID], 
                                                  $node[MIDCOM_NAV_NAME], 
                                                  $node[MIDCOM_NAV_URL], 
                                                  $node[MIDCOM_NAV_COMPONENT], 
                                                  $node[MIDCOM_NAV_ICON]);
                    } else {
                        $inner .= $this->create_menu_nodes($nodeid, $level, $currpath);
                    }
                }
            }

            if ($leaves && $this->_show_leaves)
            {
                foreach ($leaves as $key => $leafid)
                {
                    $leaf = $this->_nav->get_leaf($leafid);
                    $inner .= $this->add_leaf($leaf[MIDCOM_NAV_ID], 
                                                  $leaf[MIDCOM_NAV_NAME], 
                                                  $leaf[MIDCOM_NAV_URL], 
                                                  $leaf[MIDCOM_NAV_COMPONENT], 
                                                  $leaf[MIDCOM_NAV_ICON]);
                }
            }

            if ($inner != '')
            {
                $html .= "<ul>\n $inner \n</ul>\n </li>";
            }
            elseif ($root != '0')
            {
                $html .= "</li>\n";
            }

        }
        elseif ($obj && !$onlysub)
        {
            $html .= $this->add_empty_folder( $obj[MIDCOM_NAV_ID], 
                                              $obj[MIDCOM_NAV_NAME], 
                                              $obj[MIDCOM_NAV_URL], 
                                              $obj[MIDCOM_NAV_COMPONENT], 
                                              $obj[MIDCOM_NAV_ICON]);
            
        }
        
        debug_pop();
        return $html;

    }


    function get_link($id, $name, $url, $component = "") 
    {
        $node_name = substr($name, 0, 30);
        if ($node_name != $name)
        {
            $node_name .= "..."; 
        }
        
        return "<a class='nav_nodeLink' alt='{$name}' title='{$name}' href='{$this->_prefix}{$component}/{$url}' >{$node_name}</a>";
    }
    /**
     * Add a folder with no leaves or subnodes
     * @param int id
     * @param string name
     * @param string url
     * @param string component
     * @param mixed string icon filename or null.
     */    
    function add_empty_folder ($id, $name, $url, $component , $icon) {
        debug_add("Adding " . __FUNCTION__ . " with nodename $name");
        $style = "";
        if ($icon !== null)
        {
            $style = "style='background:url( ".MIDCOM_STATIC_URL."/".$icon.") center left no-repeat;'";
        }
        $node_name = substr($name, 0, 30);
        if ($node_name != $name)
        {
            $node_name .= "..."; 
        }
        $html = "<li $style class='nav_folder'>\n";
        $html .= $this->get_link($id, $name,$url, $component, $style);
        $html .= '</li>';
        return $html;
    }
    /**
     * Add html for a leaf
     * @see add_empty_folder
     */
    function add_leaf($id, $name, $url, $component, $icon) 
    {
        debug_add("Adding " . __FUNCTION__ . " with nodename $name");
        $style = "";
        if ($icon !== null)
        {
            $style = "style='background:url( ".MIDCOM_STATIC_URL."/".$icon.") center left no-repeat;'";
        }
        $html  = "<li class='nav_item' >\n";
        $html .= $this->get_link($id, $name,$url, $component, $style);
        $html .= "\n</li>";
        
        return $html;
    }
    /**
     * Add a node that is open and showing subleaves and nodes
     * @see add_empty_folder
     */
    function add_open_node($id, $name, $url, $component , $icon = null) 
    {
        debug_add("Adding " . __FUNCTION__ . " with nodename $name");
        $style = "";
        if ($icon !== null)
        {
            $style = "style='background:url( ".MIDCOM_STATIC_URL."/".$icon.") center left no-repeat;'";
        }
        $html = "<li $style class='nav_openFolder' id='i$id'>\n";
        $html .= "<a href='#'  class='nav_openFolder' onclick=\"openCloseUl(event)\"  id='ai$id' ></a>";
        $html .= $this->get_link($id, $name, $url, $component, $style );
        return $html;
    }
    /**
     * Adds a closed subnode with content
     * @see add_empty_folder
     */
    function add_closed_node_with_content ($id, $name, $url, $component , $icon = null) 
    {
        debug_add("Adding " . __FUNCTION__ . " with nodename $name");
        $style = "";
        if ($icon !== null)
        {
            $style = "style='background:url( ".MIDCOM_STATIC_URL."/".$icon.") center left no-repeat;'";
        }
        $html = "<li $style class='nav_closedFolder' id='i{$id}'>\n";
        $html .= "<a href='#'  class='nav_closedFolder' onclick=\"openCloseUl(event)\" ></a>";
        $html .= $this->get_link($id, $name, $url, $component  ,$style  );
        return $html;
    }
    /**
     * Add a closed folder that loads it's content with a new request.
     * @see add_empty_folder 
     */
    function add_closed_node($id, $name, $url, $component , $icon = null  )
    {
        debug_add("Adding " . __FUNCTION__ . " with nodename $name");
        $style = "";
        if ($icon !== null)
        {
            $style = "style='background:url( ".MIDCOM_STATIC_URL."/".$icon.") center left no-repeat;'";
        }
        $html = "<li $style class='nav_closedFolder' id='i{$id}'>\n";
        $html .= "<a href='#'  class='nav_closedFolder' onclick=\"getSubElements('{$this->_prefix}ajaxmenu/{$component}/{$id}/3/'," .
                "'i{$id}',event)\" id='ai{$id}' ></a>";
        return $html . $this->get_link ($id, $name,$url, $component) . "</li>";                    
    }
    /**
     * Generate the path to the current object.
     */
    function _generate_menu_path()
    { 
        $breadcrumb = $this->_nav->get_breadcrumb_array();

        for ($i = 0; $i < count($breadcrumb); $i ++)
        {
            $this->_menu_path[$breadcrumb[$i][MIDCOM_NAV_ID]] = 1;
        }
        $this->_menu_num = count($this->_menu_path);
    }

    /**
     * Output a whole menu from the root.
     * This function is called from navigation, create_nodes may also be used via the ajacmenu handler in view.php. 
     * */
     
    function to_html()
    {
        $this->set_up(); 
        $this->_generate_menu_path();
        
        $this->maxlevel = $this->_request_data['aegir_interface']->nav_maxlevel;
        
        $html = '<ul class="nav_root">';
        foreach ($this->_request_data['aegir_interface']->registry as $key => $value)
        {
            $style = "";

            if (array_key_exists('icon', $value))
            {
                $style = "style='background: url($iconurl/{$view[icon]}) center left no-repeat;'";
            }

            if ($key === $this->_request_data['aegir_interface']->current)
            {

                $html .= "<li $style class='nav_openFolder' id='i{$key}'>\n";
                $html .= "<a  class='nav_openFolder' onclick=\"openCloseUl(event)\" id='ai{$key}' ></a>\n\t <a class='nav_nodeLink' href='".$this->_prefix.$key."' >{$value['name']} </a>\n";

                $html .= $this->create_menu_nodes(0, -1);
                $html .= "</li>\n";
            }
            elseif (!(bool ) $value['hide'])
            {
                $html .= "<li $style class='nav_closedFolder' id='i{$key}'>\n";
                $html .= "<a  class='nav_closedFolder' "."onclick=\"getSubElements('{$this->_prefix}ajaxmenu/{$key}/0/1/',"."'i{$key}',event)\" id='ai{$key}' >&nbsp;</a>\n\t <a class='nav_nodeLink' href='".$this->_prefix.$key."' >{$value['name']} </a></li>\n";
            }
        }
        $html .= "</ul>";

        echo $html;
    }
    
    function to_html_simple() 
    {
        $this->set_up(); 
        $this->_generate_menu_path();
        
        $this->maxlevel = 1;
        $html = '<ul class="nav_root">';
        $root = $this->_nav->get_root_node();
        
        $html .= $this->create_menu_nodes('0',-1);
        $html .= "</ul>";
        return $html;
    }
}


?>