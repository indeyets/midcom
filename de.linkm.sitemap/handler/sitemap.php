<?php
/**
 * @package de.linkm.sitemap
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Sitemap handler class
 *
 * @package de.linkm.sitemap
 */
class de_linkm_sitemap_handler_sitemap extends midcom_baseclasses_components_handler
{
    /**
     * Root folder for the sitemap
     *
     * @access private
     * @var midcom_db_topic
     */
    var $_root_folder = null;

    /**
     *
     *
     * @var midcom_helper_nav
     */
    var $_nap = null;

    /**
     * Current node
     *
     * @access private
     */
    var $_current_node = null;

    /**
     * ID of the root folder
     *
     * @access private
     * @var integer
     */
    var $_root_node_id = null;

    /**
     * Simple constructor, calls for the parent class
     *
     * @access public
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Initializes the variables
     *
     * @access private
     */
    function _on_initialize()
    {
        $this->_request_data['skip_topics'] = $this->_config->get('skip_topics');

        $site_root = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ROOTTOPIC);
        // By default use the site root node
        $this->_root_node_id = $site_root->id;

        $root = $this->_config->get('root_topic');
        if (isset($_REQUEST['de_linkm_sitemap_set_root']))
        {
            $root = $_REQUEST['de_linkm_sitemap_set_root'];
        }

        if (!empty($root))
        {
            // User has specified a root topic to use in component config or request parameter
            $qb = midcom_db_topic::new_query_builder();
            $qb->add_constraint('guid', '=', $root);
            $qb->add_constraint('up', 'INTREE', $site_root->id);
            $topics = $qb->execute();
            if (empty($topics))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not open root topic with GUID {$root}, please check your setup: " . mgd_errstr());
                // This will exit
            }

            $this->_root_node_id = $topics[0]->id;
        }

        if (  $this->_config->get('show_levels') != ''
            && is_numeric($this->_config->get('show_levels'))
           )
        {
            $this->_show_levels = $this->_config->get('show_levels');
        }
        else
        {
            $this->_show_levels = 99;
        }
        if (   isset($_REQUEST['de_linkm_sitemap_set_levels'])
            && $_REQUEST['de_linkm_sitemap_set_levels'] < $this->_show_levels
           )
        {
            $this->_show_levels = $_REQUEST['de_linkm_sitemap_set_levels'];
        }
    }

    /**
     * Handler for xml Sitemap
     *
     * @access private
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_xml($handler_id, $args, &$data)
    {
        $_MIDCOM->cache->content->content_type("text/xml");
        $_MIDCOM->header("Content-type: text/xml; charset=UTF-8");

        $_MIDCOM->skip_page_style = true;

        return true;
    }

    /**
     * Show the content of xml Sitemap
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     * @access private
     */
    function _show_xml($handler_id, &$data)
    {
        $this->_nap = new midcom_helper_nav();
        midcom_show_style('xml-header');
        $this->_get_sitemap($this->_root_node_id);
        midcom_show_style('xml-footer');
    }

    /**
     * Get the site contents starting from the defined ID. This function will
     * determine whether to list leaves or not.
     *
     * @access private
     * @var integer
     */
    function _get_sitemap($id = null)
    {
        if (is_null($id))
        {
            $id = $this->_nap->get_current_node();
        }

        $root = $this->_nap->get_node($id);

        if (!$root)
        {
            return false;
        }

        $this->_request_data['item'] = $root;
        midcom_show_style('xml-item');

        if ($this->_config->get('hide_leaves'))
        {
            $this->_list_nodes($id);
        }
        else
        {
            $this->_list_child_elements($id);
        }
    }

    /**
     * Lists nodes
     *
     * @access private
     * @var integer
     */
    function _list_nodes($id)
    {
        foreach ($this->_nap->list_nodes($id) as $node_id)
        {
            $this->_request_data['item'] = $this->_nap->get_node($node_id);
            midcom_show_style('xml-item');

            // Call recursively
            $this->_list_nodes($node_id);
        }
    }

    /**
     * Lists all the child elements
     *
     * @access private
     * @var integer
     */
    function _list_child_elements($id)
    {
        foreach ($this->_nap->list_child_elements($id) as $element)
        {
            if ($element[MIDCOM_NAV_TYPE] === 'node')
            {
                $this->_request_data['item'] = $this->_nap->get_node($element[MIDCOM_NAV_ID]);
            }
            else
            {
                $this->_request_data['item'] = $this->_nap->get_leaf($element[MIDCOM_NAV_ID]);
            }

            if (!$element[MIDCOM_NAV_NAME])
            {
                continue;
            }

            midcom_show_style('xml-item');

            if ($element[MIDCOM_NAV_TYPE] === 'node')
            {
                $this->_list_child_elements($element[MIDCOM_NAV_ID]);
            }
        }
    }

    /**
     * Sets the page title and checks if the current user has permissions to
     * change the configuration.
     *
     * @access private
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_sitemap($handler_id, $args, &$data)
    {
        $data['view_title'] = $this->_topic->extra;
        $_MIDCOM->set_pagetitle($data['view_title']);

        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => 'config.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            ));
        }

        $tmp = array();
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     * Shows the sitemap
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     * @access private
     */
    function _show_sitemap($handler_id, &$data)
    {
        $this->_nap = new midcom_helper_nav();
        $data['depth'] = 0;

        midcom_show_style('begin-sitemap');
        midcom_show_style('enter-level');

        if ($this->_config->get('display_root'))
        {
            if (!$this->_show_node($this->_root_node_id, $data))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "An error occurred: de_linkm_sitemap_viewer::_show_node({$this->_root_node_id}) returned false. Aborting.");
            }
        }
        else
        {
            $subnodes = $this->_nap->list_nodes($this->_root_node_id);
            if ($subnodes === false)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'An error occurred: de_linkm_sitemap_viewer::show: Could not list root\'s subnodes');
            }

            foreach ($subnodes as $id)
            {
                if (!$this->_show_node($id, $data))
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "An error occurred: de_linkm_sitemap_viewer::_show_node({$id}) returned false. Aborting.");
                }
            }

        }

        midcom_show_style('leave-level');
        midcom_show_style('end-sitemap');
    }

    function _show_node($nodeid, &$data)
    {
        // Load the node
        $previous = $this->_current_node;
        $this->_current_node = $this->_nap->get_node($nodeid);
        $topics_to_skip = explode(',',$this->_config->get('skip_topics'));
        if(!in_array($this->_current_node[MIDCOM_NAV_GUID],$topics_to_skip))
        {

            // Start a new node and display it
            $data['node'] = $this->_current_node;
            midcom_show_style('node-start');
            midcom_show_style('node');

            // Try to load Child elements
            $subnodes = $this->_nap->list_nodes($nodeid);
            $leaves = null;
            if ($subnodes === false)
            {
                midcom_show_style('node-end');
                return false;
            }
            if ($this->_config->get('hide_leaves') == false)
            {
                $leaves = $this->_nap->list_leaves($nodeid, $data);
                if ($leaves === false)
                {
                    midcom_show_style('node-end');
                    return false;
                }
            }

            // Now display all subnodes and the leaves in the right order
            if ($this->_config->get('leaves_first'))
            {
                if (! $this->_show_leaves($leaves, $data))
                {
                    midcom_show_style('node-end');
                    return false;
                }

                if (!$this->_show_subnodes($subnodes, $data))
                {
                    midcom_show_style('node-end');
                    return false;
                }
            }
            else
            {
                if (! $this->_show_subnodes($subnodes, $data))
                {
                    midcom_show_style('node-end');
                    return false;
                }

                if (! $this->_show_leaves($leaves, $data))
                {
                    midcom_show_style('node-end');
                    return false;
                }
            }

            // Close current node
            midcom_show_style('node-end');

            // Clean up
            $this->_current_node = $previous;
        }
        return true;
    }

    function _show_leaves($leaves, &$data)
    {
        if (is_null($leaves))
        {
            // hide_leaves seems to be set.
            return true;
        }

        if (count($leaves) > 0)
        {
            // Begin leaves listing
            midcom_show_style('begin-leaves');

            // Iterate over the leaves and display them
            foreach ($leaves as $id)
            {
                $data['leaf'] = $this->_nap->get_leaf($id);
                if (   $this->_config->get('hide_index_articles')
                    && $data['leaf'][MIDCOM_NAV_URL] == '')
                {
                    // This is an index article, skip
                    continue;
                }

                midcom_show_style('leaf');
            }

            // End leaves listing
            midcom_show_style('end-leaves');
        }

        return true;
    }

    function _show_subnodes($subnodes, &$data)
    {
        if (count($subnodes) > 0)
        {
            // First we have to descend a level for the subnode-listing
            $data['depth']++;
            if($this->_show_levels > $data['depth'])
            {
            midcom_show_style('enter-level');

            // Iterate over the nodes and display them
            foreach ($subnodes as $id)
            {
                if (!$this->_show_node($id, $data))
                {
                    $data['depth']--;
                    midcom_show_style('leave-level');
                    return false;
                }
            }

            // Finally we have to ascend back up to the previous level
            midcom_show_style('leave-level');
            }
            $data['depth']--;
        }

        return true;
    }

    function list_root_nodes()
    {
        $nap = new midcom_helper_nav();

        $root_nodes = array();
        $root_nodes[''] = '';

        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('up', '=', $nap->get_root_node());
        $qb->add_order('score');
        $qb->add_order('name');
        $nodes = $qb->execute();
        foreach ($nodes as $node)
        {
            $root_nodes[$node->guid] = $node->extra;
        }

        return $root_nodes;
    }
}
?>