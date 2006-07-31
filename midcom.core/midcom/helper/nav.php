<?php
/**
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:nav.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Main Navigation interface class.
 *
 * Basically, this class proxies all requests to a midcom_helper__basicnav
 * class. See the interface definition of it for further details.
 *
 * Additionally this class implements a couple of helper functions to make
 * common NAP tasks easier.
 *
 * <b>Important note:</b> Whenever you add new code to this class, or extend it through
 * inheritance, never call the proxy-functions of basicnav directly, this is strictly
 * forbidden.
 *
 * @todo End-User documentation of node and leaf data, as the one in basicnav is incomplete too.
 * @package midcom
 * @see midcom_helper__basicnav
 */
class midcom_helper_nav {

    /**
     * A reference to the basicnav instance in use.
     *
     * @var midcom_helper__basicnav
     * @access private
     */
    var $_basicnav;

    /**
     * The context ID we're accociated with.
     *
     * @var int
     * @access private
     */
    var $_contextid;

    /**
     * Create a NAP instance for the given context. If unspecified, it
     * uses the currently active context which should be sufficient
     * in most cases.
     *
     * @param int $contextid	The id of the context you want to navigate.
     */
    function midcom_helper_nav($contextid = -1)
    {
        if ($contextid == -1)
        {
            $contextid = $GLOBALS['midcom']->get_current_context();
        }
        $this->_basicnav =& $GLOBALS["midcom"]->get_basic_nav($contextid);
        $this->_contextid = $contextid;
    }


    /* The following methods are just interfaces to midcom_helper__basicnav */

    /**
     * Retrieve the ID of the currently displayed node. Defined by the topic of
     * the component that declared able to handle the request.
     *
     * @return int	The ID of the node in question.
     * @see midcom_helper__basicnav::get_current_node()
     */
    function get_current_node () {
        return $this->_basicnav->get_current_node();
    }

    /**
     * Retrieve the ID of the currently displayed leaf. This is a leaf that is
     * displayed by the handling topic. If no leaf is active, this function
     * returns FALSE. (Remeber to make a type sensitve check, e.g.
     * nav::get_current_leaf() !== false to distinguish "0" and "false".)
     *
     * @return int	The ID of the leaf in question or false on failure.
     * @see midcom_helper__basicnav::get_current_leaf()
     */
    function get_current_leaf () {
        return $this->_basicnav->get_current_leaf();
    }

    /**
     * Retrieve the ID of the root node. Note that this ID is dependent from the
     * ID of the MidCOM Root topic and therefore will change as easily as the
     * root topic ID might. The MIDCOM_NAV_URL entry of the root node's data will
     * always be empty.
     *
     * @return int	The ID of the root node.
     * @see midcom_helper__basicnav::get_root_node()
     */
    function get_root_node () {
        return $this->_basicnav->get_root_node();
    }

    /**
     * Lists all Sub-nodes of $parent_node. If there are no subnodes you will get
     * an empty array, if there was an error (for instance an unkown parent node
     * ID) you will get FALSE.
     *
     * @param int $parent_node	The id of the node of which the subnodes are searched.
     * @param bool $show_noentry Show all objects on-site which have the noentry flag set.
     *     This parameter has no effect in AIS. This defaults to false.
     * @return Array			An Array of Node IDs or false on failure.
     * @see midcom_helper__basicnav::list_nodes()
     */
    function list_nodes ($parent_node, $show_noentry = false)
    {
        return $this->_basicnav->list_nodes($parent_node, $show_noentry);
    }

    /**
     * Lists all leaves of $parent_node. If there are no leaves you will get an
     * empty array, if there was an error (for instance an unknown parent node ID)
     * you will get FALSE.
     *
     * @param int $parent_node	The ID of the node of which the leaves are searched.
     * @param bool $show_noentry Show all objects on-site which have the noentry flag set.
     *     This parameter has no effect in AIS. This defaults to false.
     * @return Array 			A list of leaves found, or false on failure.
     * @see midcom_helper__basicnav::list_leaves()
     */
    function list_leaves ($parent_leaf, $show_noentry = false)
    {
        return $this->_basicnav->list_leaves($parent_leaf, $show_noentry);
    }

    /**
     * This will give you a key-value pair describeing the node with the ID
     * $node_id. The defined keys are described above in Node data interchange
     * format. You will get false if the node ID is invalid.
     *
     * @param int $node_id	The node-id to be retrieved.
     * @return Array		The node-data as outlined in the class introduction, false on failure
     * @see midcom_helper__basicnav::get_node()
     */
    function get_node ($node_id) {
        return $this->_basicnav->get_node($node_id);
    }

    /**
     * This will give you a key-value pair describeing the leaf with the ID
     * $node_id. The defined keys are described above in leaf data interchange
     * format. You will get false if the leaf ID is invalid.
     *
     * @param string $leaf_id	The leaf-id to be retrieved.
     * @return Array		The leaf-data as outlined in the class introduction, false on failure
     * @see midcom_helper__basicnav::get_leaf()
     */
    function get_leaf ($leaf_id) {
        return $this->_basicnav->get_leaf($leaf_id);
    }

    /**
     * Returns the ID of the node to which $leaf_id is accociated to, false
     * on failure.
     *
     * @param string $leaf_id	The Leaf-ID to search an uplink for.
     * @return int 			The ID of the Node for which we have a match, or false on failure.
     * @see midcom_helper__basicnav::get_leaf_uplink()
     */
    function get_leaf_uplink ($leaf_id) {
        return $this->_basicnav->get_leaf_uplink($leaf_id);
    }

    /**
     * Returns the ID of the node to which $node_id is accociated to, false
     * on failure. The root node's uplink is -1.
     *
     * @param int $node_id	The Leaf-ID to search an uplink for.
     * @return int 			The ID of the Node for which we have a match, -1 for the root node, or false on failure.
     * @see midcom_helper__basicnav::get_node_uplink()
     */
    function get_node_uplink ($node_id) {
        return $this->_basicnav->get_node_uplink($node_id);
    }

    /**
     * Checks if the given node is within the tree of another node.
     *
     * @param int	$node_id	The node in question.
     * @param int	$root_node	The root node to use.
     * @return bool				True, if the node is a subnode of the root node, false otherwise.
     */
    function is_node_in_tree ($node_id, $root_id) {
        return mgd_is_in_topic_tree ($root_id, $node_id);
    }

    /**
     * This function returns the toolbar definition for the NAP object passed. This must
     * superseed all calls to $nap_object[MIDCOM_NAV_TOOLBAR] as this information is
     * not usable directly - it would result from the MidCOM cache and thus be out of date
     * always (and especially not adapted to the current user).
     *
     * <b>Implementation note:</b> This is a hotfix, that works around the current problem.
     * It is not very performant if you query large numbers of NAP toolbars at this time.
     * We have some basic caching in this function, but nevertheless we need a better solution
     * here.
     *
     * @param Array $nap_object The NAP data structure of the object for which a toolbar should be retrieved.
     * @return Array Toolbar definition array as originally stored in the MIDCOM_NAV_TOOLBAR key (so this can be null as well).
     */
    function get_toolbar_definition ($nap_object)
    {
        return $this->_basicnav->get_toolbar_definition($nap_object);
    }

    /**
     * List all child elements, nodes and leaves alike, of the node with ID
     * $parent_node_id. For every child element, an array of ID and type (node/leaf)
     * is given as
     *
     * - MIDCOM_NAV_ID => 0,
     * - MIDCOM_NAV_TYPE => "node"
     *
     * If there are no child elements at all the method will return an empty array,
     * in case of an error FALSE.  NOTE: This method should be quite slow, there's
     * room for improvement... :-)
     *
     * @param int $parent_node_id	The ID of the parent node.
     * @return Array				A list of found elements, or false on failure.
     */
    function list_child_elements($parent_node_id)
    {
        debug_push("nav::list_child_elements");

        // Fetch nodes and leaves
        if (! is_numeric($parent_node_id))
        {
            debug_add("Parameter passed is no integer: [$parent_node_id]", MIDCOM_LOG_ERROR);
            debug_print_type('Type was:', $parent_node_id);
            debug_pop();
            return false;
        }

        $parent_topic = mgd_get_topic($parent_node_id);
        if (! $parent_topic)
        {
            return FALSE;
        }

        $navorder = $parent_topic->parameter("midcom.helper.nav", "navorder");

        switch ($navorder)
        {
            case MIDCOM_NAVORDER_DEFAULT:
                $navorder = 'topicsfirst';
                break;

            case MIDCOM_NAVORDER_TOPICSFIRST:
                $navorder = 'topicsfirst';
                break;

            case MIDCOM_NAVORDER_ARTICLESFIRST:
                $navorder = 'articlesfirst';
                break;

            case MIDCOM_NAVORDER_SCORE:
                $navorder = 'score';
                break;

            default:
                $navorder = 'topicsfirst';
                break;
        }

        $nav_object = midcom_helper_itemlist::factory($navorder, $this, $parent_topic);
        $result = $nav_object->get_sorted_list();

        debug_pop();
        return $result;
    }

    /**
     * This function tries to resolve a guid into an NAP object.
     *
     * The code is optimized trying to avoid a full-scan if possible. To do this it
     * will treat topic and article guids specially: In both cases the system will
     * translate it using the topic id into a node id and scan only that part of the
     * tree non-recursivly.
     *
     * A full scan of the NAP data is only done if another MidgardObject is used.
     *
     * Note: If you want to resolve a GUID you got from a Permalink, use the Permalinks
     * service within MidCOM, as it covers more objects then the NAP listings.
     *
     * @param string $guid The GUID of the object to be looked up.
     * @return mixed Eitehr a node or leaf structure, distinguishable by MIDCOM_NAV_TYPE, or false on failure.
     * @see midcom_services_permalinks
     */
    function resolve_guid ($guid)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Checking GUID {$guid}...");

        // First, check if the GUID is already known by basicnav:

        $cached_result = $this->_basicnav->get_loaded_object_by_guid($guid);
        if (! is_null($cached_result))
        {
            debug_add('The GUID was already known by the basicnav instance, returning the cached copy directly.');
            debug_pop();
            return $cached_result;
        }

        // Fetch the object in question for a start, so that we know what to do (tm)
        // Note, that objects that cannot be resolved will still be processed using a full-scan of
        // the tree. This is, for example, used by the on-delete cache invalidation.
        $object = $_MIDCOM->dbfactory->get_object_by_guid($guid);
        if (! $object)
        {
            debug_add("Could not load GUID {$guid}, trying to continue anyway. Last error was: " . mgd_errstr(), MIDCOM_LOG_INFO);
        }

        if (   $object
            && $object->__table__ == 'topic')
        {
            debug_add("This is a topic.");

            // Ok. This topic should be within the content tree,
            // we check this and return the node if everything is ok.
            if (! $this->is_node_in_tree($object->id, $this->get_root_node()))
            {
                debug_add("NAP::resolve_guid: The Guid {$guid} leads to an unknown topic not in our tree.", MIDCOM_LOG_WARN);
                debug_print_r("Retrieved topic was:", $object);
                debug_pop();
                return false;
            }
            debug_pop();
            return $this->get_node($object->id);
        }

        if (   $object
            && $object->__table__ == 'article')
        {
            debug_add("This is an article.");

            // Ok, let's try to find the article using the topic in the tree.
            if (! $this->is_node_in_tree($object->topic, $this->get_root_node()))
            {
                debug_add("NAP::resolve_guid: The Guid {$guid} leads to an unknown topic not in our tree.", MIDCOM_LOG_WARN);
                debug_print_r("Retrieved article was:", $object);
                debug_pop();
                return false;
            }

            $topic = mgd_get_topic($object->topic);
            if (! $topic)
            {
                $GLOBALS['midcom']->generate_error(
                    MIDCOM_ERRCRIT,
                    "Data inconsistency, the topic ID ({$object->topic}) of the article {$object->id} is invalid. "
                        . 'Last error was: ' . mgd_errstr());
                // This will exit.
            }

            $leaves = $this->list_leaves($object->topic, true);
            foreach ($leaves as $leafid)
            {
                $leaf = $this->get_leaf($leafid);
                if ($leaf[MIDCOM_NAV_GUID] == $guid)
                {
                    debug_pop();
                    return $leaf;
                }
            }

            debug_add("The Article GUID {$guid} is somehow hidden from the NAP data in its topic, no results shown.", MIDCOM_LOG_INFO);
            debug_print_r("Retrieved article was:", $object);
            debug_pop();
            return false;
        }

        // this is the rest of the lot, we need to traverse everything, unfortunalety.
        // First, we traverse a list of nodes to be checked on by one, avoiding a recursive
        // function call.

        debug_add("This is something else, we'll do a full scan.");

        $unprocessed_node_ids = Array ($this->get_root_node());
        while ( count ($unprocessed_node_ids) > 0)
        {
            $node_id = array_shift($unprocessed_node_ids);

            // Check leaves of this node first.
            $leaves = $this->list_leaves($node_id, true);
            foreach ($leaves as $leafid)
            {
                $leaf = $this->get_leaf($leafid);
                if ($leaf[MIDCOM_NAV_GUID] == $guid)
                {
                    debug_pop();
                    return $leaf;
                }
            }

            // Ok, append all subnodes to the queue.
            $unprocessed_node_ids = array_merge($unprocessed_node_ids, $this->list_nodes($node_id));
        }

        debug_add("We were unable to find the GUID {$guid} in the MidCOM tree even with a full scan.");
        debug_pop();
        return false;
    }


    /* The more complex interface methods starts here */

    /**
     * This function provides an interface to construct links like "View this page".
     *
     * It takes the currently displayed content
     * element (either a leaf or node) and constructs the respective URL relative to
     * the root of the website as passed to the function.
     *
     * @param string	$baseurl	The base url that leads to the root page of the MidCOM site.
     * @return string	The full URL to the on-site element in question, null if there is no on-site representation, false on failure
     */
    function view_current_page_url ($baseurl)
    {
        // Go upwards step by step and build together the page view URL
        // up to the root topic.
        $url = "";
        if ($this->get_current_leaf() !== false)
        {
            $leaf = $this->get_leaf($this->get_current_leaf());
            if (is_null($leaf[MIDCOM_NAV_SITE]))
            {
                return null;
            }
            $url = $leaf[MIDCOM_NAV_SITE][MIDCOM_NAV_URL];
        }
        $nid = $this->get_current_node();

        do
        {
            $node = $this->get_node($nid);
            $url = $node[MIDCOM_NAV_URL] . $url;
            $nid = $this->get_node_uplink($nid);
            if ($nid === false)
            {
                debug_add("get_node_uplink failed; view_this_page_url aborting.");
                return false;
            }
        }
        while($nid != -1);

        if (substr($baseurl, -1) == "/")
        {
            return $baseurl . $url;
        }

        return $baseurl . "/" . $url;
    }

    /**
     * This function provides an interface to construct links like "Edit this page".
     *
     * It takes the currently displayed content
     * element (either a leaf or node) and constructs the respective URL relative to
     * the root of the AIS instance as passed to the function.
     *
     * @param string	$baseurl	The base url that leads to the root AIS page of the MidCOM site (without any /$id/data additions).
     * @return string	The full URL to the AIS element in question, null if there is no on-site representation, false on failure
     */
    function edit_current_page_url ($baseurl)
    {
        $url = "";
        // First build up the topic edit URL
        $nid = $this->get_current_node();
        $url = "$nid/data/";
        if ($this->get_current_leaf() !== false)
        {
            $leaf = $this->get_leaf($this->get_current_leaf());
            if (is_null($leaf[MIDCOM_NAV_ADMIN]))
            {
                return null;
            }
            $url .= $leaf[MIDCOM_NAV_ADMIN][MIDCOM_NAV_URL];
        }
        if (substr($baseurl, -1) == "/")
        {
            return $baseurl . $url;
        }
        return $baseurl . "/" . $url;
    }

    /**
     * Compute a toolbar out of the NAP information for a given node.
     *
     * You can either pass a node structure, a node id or null. I the last
     * case the current node is used.
     *
     * The information from the component will be post-processed so that
     * the URLs point to the right location in NAP.
     *
     * It uses _check_toolbar_permission to ascertain if the user can edit
     * a node.
     *
     * @param string $baseurl The base url that leads to the root AIS page of the MidCOM site (without any /$id/data additions).
     * @param mixed $node The node to be processed, either a fetched node array, a node id or null for the current node.
     * @return midcom_helper_toolbar The created toolbar.
     * @see midcom_helper_nav::_check_toolbar_permission()
     */
    function get_node_toolbar ($baseurl, $node = null) {
        // Check Parameter
        if (is_numeric($node))
        {
            $node = $this->get_node($node);
        }
        else if (is_null($node))
        {
            $node = $this->get_node($this->get_current_node());
        }
        else if (! is_array($node))
        {
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, 'get_node_toolbar: The parameter $node could not be verified as a NAP node.');
        }

        // Check permissions first
        if (! $this->_check_toolbar_permissions($node[MIDCOM_NAV_ID]))
        {
            // Return an empty toolbar if we don't have the right permissions.
            return new midcom_helper_toolbar();
        }

        // Calculate Prefix
        if (substr($baseurl, -1) != '/')
        {
            $baseurl .= '/';
        }
        $prefix = "{$baseurl}{$node[MIDCOM_NAV_ID]}/data/";

        $toolbar_definition = $this->get_toolbar_definition($node);

        // Check for emptyness, if not, sort array to be sure to have the right order
        if ($toolbar_definition == null)
        {
            return new midcom_helper_toolbar();
        }
        ksort($toolbar_definition, SORT_NUMERIC);

        // Generate Toolbar and return it
        $toolbar = new midcom_helper_toolbar();
        foreach ($toolbar_definition as $item)
        {
            // Some items may want to use non-AIS links
            $direct_link = false;
            if (   array_key_exists(MIDCOM_TOOLBAR_OPTIONS, $item)
                && array_key_exists("rel", $item[MIDCOM_TOOLBAR_OPTIONS])
                && $item[MIDCOM_TOOLBAR_OPTIONS]["rel"] == "directlink")
            {
                $direct_link = true;
            }

            if ($direct_link)
            {
                $item[MIDCOM_TOOLBAR_URL] = $item[MIDCOM_TOOLBAR_URL];
            }
            else
            {
                $item[MIDCOM_TOOLBAR_URL] = $prefix . $item[MIDCOM_TOOLBAR_URL];
            }
            $toolbar->add_item($item);
        }
        return $toolbar;
    }

    /**
     * Shortcut function, which creates a node-toolbar and renders it
     * immediately.
     *
     * @param string $baseurl The base url that leads to the root AIS page of the MidCOM site (without any /$id/data additions).
     * @param mixed $node The node to be processed, either a fetched node array, a node id or null for the current node.
     * @return string The rendered toolbar.
     * @see midcom_helper_nav::get_node_toolbar()
     */
    function render_node_toolbar($baseurl, $node = null) {
        $toolbar = $this->get_node_toolbar($baseurl, $node);
        return $toolbar->render();
    }

    /**
     * Compute a toolbar out of the NAP information for a given leaf.
     *
     * You can either pass a leaf structure, a leaf id or null. I the last
     * case the current leaf is used. If the current leaf is undefined,
     * the class will return null.
     *
     * The information from the component will be post-processed so that
     * the URLs point to the right location in NAP.
     *
     * It uses _check_toolbar_permission to ascertain if the user can edit
     * a leaf.
     *
     * @param string $baseurl The base url that leads to the root AIS page of the MidCOM site (without any /$id/data additions).
     * @param mixed $leaf The node to be processed, either a fetched leaf array, a leaf id or null for the current leaf.
     * @return midcom_helper_toolbar The created toolbar or null if no current leaf toolbar is available.
     * @see midcom_helper_nav::_check_toolbar_permission()
     */
    function get_leaf_toolbar ($baseurl, $leaf = null) {
        // Check Parameter
        if (is_numeric($leaf))
        {
            $leaf = $this->leaf($node);
        }
        else if (is_null($leaf))
        {
            $leafid = $this->get_current_leaf();
            if ($leafid === false)
            {
                // No leaf is selected, returning null.
                return null;
            }
            $leaf = $this->get_leaf($leafid);

        }
        else if (! is_array($leaf))
        {
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, 'get_leaf_toolbar: The parameter $leaf could not be verified as a NAP leaf.');
        }

        // Check permissions first
        if (! $this->_check_toolbar_permissions($leaf[MIDCOM_NAV_NODEID]))
        {
            // Return an empty toolbar if we don't have the right permissions.
            return new midcom_helper_toolbar();
        }

        // Calculate Prefix
        if (substr($baseurl, -1) != '/')
        {
            $baseurl .= '/';
        }
        $prefix = "{$baseurl}{$leaf[MIDCOM_NAV_NODEID]}/data/";

        $toolbar_definition = $this->get_toolbar_definition($leaf);

        // Check for emptyness, if not, sort array to be sure to have the right order
        if ($toolbar_definition == null)
        {
            return new midcom_helper_toolbar();
        }

        ksort($toolbar_definition, SORT_NUMERIC);

        // Generate Toolbar and return it
        $toolbar = new midcom_helper_toolbar();
        foreach ($toolbar_definition as $item)
        {
            // Some items may want to use non-AIS links
            $direct_link = false;
            if (   array_key_exists(MIDCOM_TOOLBAR_OPTIONS, $item)
                && array_key_exists("rel", $item[MIDCOM_TOOLBAR_OPTIONS])
                && $item[MIDCOM_TOOLBAR_OPTIONS]["rel"] == "directlink")
            {
                $direct_link = true;
            }

            if ($direct_link)
            {
                $item[MIDCOM_TOOLBAR_URL] = $item[MIDCOM_TOOLBAR_URL];
            }
            else
            {
                $item[MIDCOM_TOOLBAR_URL] = $prefix . $item[MIDCOM_TOOLBAR_URL];
            }
            $toolbar->add_item($item);
        }
        return $toolbar;
    }

    /**
     * Shortcut function, which creates a leaf-toolbar and renders it
     * immediately.
     *
     * @param string $baseurl The base url that leads to the root AIS page of the MidCOM site (without any /$id/data additions).
     * @param mixed $leaf The node to be processed, either a fetched leaf array, a leaf id or null for the current leaf.
     * @return string The rendered toolbar or null, if none was available.
     * @see midcom_helper_nav::get_leaf_toolbar()
     */
    function render_leaf_toolbar($baseurl, $leaf = null) {
        $toolbar = $this->get_leaf_toolbar($baseurl, $leaf);
        if (is_null($toolbar))
        {
            return null;
        }
        return $toolbar->render();
    }

    /**
     * Compute a toolbar out of the NAP information for a given leaf and
     * its node. Merges both toolbars into one. Always based on a leaf.
     *
     * You can either pass a leaf structure, a leaf id or null. I the last
     * case the current leaf is used. If the current leaf is undefined,
     * the class will return the current node toolbar instead.
     *
     * The information from the component will be post-processed so that
     * the URLs point to the right location in NAP.
     *
     * It uses _check_toolbar_permission to ascertain if the user can edit
     * the leaf/node.
     *
     * @param string $baseurl The base url that leads to the root AIS page of the MidCOM site (without any /$id/data additions).
     * @param mixed $leaf The node to be processed, either a fetched leaf array, a leaf id or null for the current leaf.
     * @return midcom_helper_toolbar The created toolbar.
     * @see midcom_helper_nav::_check_toolbar_permission()
     */
    function get_combined_toolbar ($baseurl, $leaf = null) {
        // Check Parameter
        if (is_null($leaf))
        {
            $leafid = $this->get_current_leaf();
            if ($leafid === false)
            {
                // No leaf is selected, returning node toolbar.
                return $this->get_node_toolbar($baseurl);
            }
            $leaf = $this->get_leaf($leafid);

        }
        else if (! is_array($leaf))
        {
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, 'get_leaf_toolbar: The parameter $leaf could not be verified as a NAP leaf.');
        }

        // Retrieve corresponding node
        $node = $this->get_node($leaf[MIDCOM_NAV_NODEID]);

        // Check permissions first
        if (! $this->_check_toolbar_permissions($node[MIDCOM_NAV_ID]))
        {
            // Return an empty toolbar if we don't have the right permissions.
            return new midcom_helper_toolbar();
        }

        // Calculate Prefix
        if (substr($baseurl, -1) != '/')
        {
            $baseurl .= '/';
        }
        $prefix = "{$baseurl}{$leaf[MIDCOM_NAV_NODEID]}/data/";

        $leaf_toolbar_definition = $this->get_toolbar_definition($leaf);
        $node_toolbar_definition = $this->get_toolbar_definition($node);

        // Merge and sort the two toolbars, check for emptyness first.
        // Do not use array_merge, it would destroy the numeric indexes,
        // see the PHP array_merge docs.
        if (! is_array($leaf_toolbar_definition))
        {
            $leaf_toolbar_definition = Array();
        }
        if (! is_array($node_toolbar_definition))
        {
            $node_toolbar_definition = Array();
        }

        $items = array_merge($leaf_toolbar_definition, $node_toolbar_definition);
        ksort($items, SORT_NUMERIC);

        // Generate Toolbar and return it
        $toolbar = new midcom_helper_toolbar();
        foreach ($items as $item)
        {
            // Some items may want to use non-AIS links
            $direct_link = false;
            if (   array_key_exists(MIDCOM_TOOLBAR_OPTIONS, $item)
                && array_key_exists("rel", $item[MIDCOM_TOOLBAR_OPTIONS])
                && $item[MIDCOM_TOOLBAR_OPTIONS]["rel"] == "directlink")
            {
                $direct_link = true;
            }

            if ($direct_link)
            {
                $item[MIDCOM_TOOLBAR_URL] = $item[MIDCOM_TOOLBAR_URL];
            }
            else
            {
                $item[MIDCOM_TOOLBAR_URL] = $prefix . $item[MIDCOM_TOOLBAR_URL];
            }
            $toolbar->add_item($item);
        }
        return $toolbar;
    }

    /**
     * Shortcut function, which creates a combined leaf/node-toolbar and renders it
     * immediately.
     *
     * @param string $baseurl The base url that leads to the root AIS page of the MidCOM site (without any /$id/data additions).
     * @param mixed $leaf The node to be processed, either a fetched leaf array, a leaf id or null for the current leaf.
     * @return string The rendered toolbar.
     * @see midcom_helper_nav::get_combined_toolbar()
     */
    function render_combined_toolbar($baseurl, $leaf = null) {
        $toolbar = $this->get_combined_toolbar($baseurl, $leaf);
        return $toolbar->render();
    }

    /**
     * Checks for the toolbar permissions, uses the cache variables
     * $_user and $_admin to cache the data against
     * repeated calls.
     *
     * @param int $topic_id The topic id to check against.
     */
    function _check_toolbar_permissions($topic_id)
    {
        return true;
        /*
         * Temporarily disabled
         *
         * Most probably this should be removed completly once we are fully moved to
         * ACL in the components.
         *
        $midgard = mgd_get_midgard();

        if (   $midgard->admin
            || mgd_is_topic_owner($topic_id))
        {
            return true;
        }

        $topic = mgd_get_topic($topic_id);
        if (! $topic)
        {
            debug_add("Failed to fetch toolbar topic ID {$topic_id}, returning false:" . mgd_errstr(), MIDCOM_LOG_INFO);
            return false;
        }

        $component = $topic->parameter('midcom', 'component');
        switch ($component)
        {
            case 'net.nemein.personnel':
                // Always allow access for this component, it has its own
                // permission checks.
                return true;

            default:
                return false;
        }
        */
    }

    /**
     * Construct a breadcrumb line.
     *
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
     * @param string	$separator		The separator to use between the elements.
     * @param string	$class			If not-null, it will be assigned to all A tags.
     * @param int		$skip_levels	The number of topic levels to skip before starting to work (use this to skip "Home" links etc.).
     * @param string	$current_class	The class that should be assigned to the currently active element.
     * @return string	The computed breadrumb line.
     */
    function get_breadcrumb_line ($separator = " &gt; ", $class = null, $skip_levels = 0, $current_class = null)
    {
        $breadcrumb_data = $this->get_breadcrumb_data();
        $result = '';

        // We traverse this list using the iterator of the array, since this allows
        // us direct treatment of the final element.
        reset($breadcrumb_data);

        // Detect real starting Node
        if ($skip_levels > 0)
        {
            if ($skip_levels >= count($breadcrumb_data))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add('We were asked to skip all (or even more) breadcrumb elements then there were present. Returning an empty breadcrumb line therefore.', MIDCOM_LOG_INFO);
                debug_pop();
                return '';
            }
            for ($i = 0; $i < $skip_levels; $i++)
            {
                next($breadcrumb_data);
            }
        }

        while(current($breadcrumb_data) !== false)
        {
            $data = current($breadcrumb_data);
            $data[MIDCOM_NAV_NAME] = htmlspecialchars($data[MIDCOM_NAV_NAME]);

            // Add the next element sensitive to the fact wether we are at the end or not.
            if (next($breadcrumb_data) === false)
            {
                if ($current_class !== null)
                {
                    $result .= "<span class='{$current_class}'>{$data[MIDCOM_NAV_NAME]}</span>";
                }
                else
                {
                    $result .= $data[MIDCOM_NAV_NAME];
                }
            }
            else
            {
                $result .= "<a href='{$data[MIDCOM_NAV_URL]}'"
                  . (is_null($class) ? '' : " class='{$class}'")
                  . ">{$data[MIDCOM_NAV_NAME]}</a>{$separator}";
            }
        }

        return $result;
    }

    /**
     * Construct source data for a breadcrumb line.
     *
     * Gives you the data needed to construct a line like
     * "Start > Topic1 > Topic2 > Article" using NAP to
     * traverse upwards till the root node. The components custom breadcrumb
     * data is inserted at the end of the computed breadcrumb line after any
     * set NAP leaf.
     *
     * See get_breadcrumb_line for a more end-user oriented way of life.
     *
     * <b>Return Value</b>
     *
     * The breadcrumb data will be returned as a list of accociative arrays each
     * containing these keys:
     *
     * - MIDCOM_NAV_URL The fully qualified URL to the node.
     * - MIDCOM_NAV_NAME The clear-text name of the node.
     * - MIDCOM_NAV_TYPE One of 'node', 'leaf', 'custom' indicating what type of entry
     *   this is.
     * - MIDCOM_NAV_ID The Identifier of the structure used to build this entry, this is
     *   either an NAP node/leaf ID or the list key set by the component for custom data.
     * - 'napobject' This contains the original NAP object retrieved by the function.
     *   Just in case you need more infromation then is available directly.
     *
     * The entry of every level is indexed by its MIDCOM_NAV_ID, where custom keys preserve
     * their original key (as passed by the component) and prefixing it with "custom-". This
     * allows you to easily check if a given node/leave is within the current breadcrumb-line
     * by checking with array_key_exists. (mgd_is_in_topic_tree was originally used for this
     * purpose, but this check is not only much faster but more flexible as it isn't limited
     * to topic).
     *
     * <b>Adding custom data</b>
     *
     * Custom elements are added to this array by using the MidCOM custom component context
     * at this time. You need to add a list with the same structure as above into the
     * custom component context key <em>midcom.helper.nav.breadcrumb</em>. (This needs
     * to be an array always, even if you return only one element.)
     *
     * Note, that the URL you pass in that list is always prepended with the current anchor
     * prefix. It is not possible to specify absolute URLs there. No leading slash is required.
     *
     * Example:
     *
     * <code>
     * $tmp = Array
     * (
     *     Array
     *     (
     *         MIDCOM_NAV_URL => "list/{$this->_category}/{$this->_mode}/1.html",
     *         MIDCOM_NAV_NAME => $this->_category_name,
     *     ),
     * );
     * $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
     * </code>
     *
     * @return array The computed breadcrumb data as outlined above.
     * @todo Maybe cache this? I don't know how complex it really is, but DB accesses are
     *     already cached by the _basicnav core. So it is not that hard.
     */
    function get_breadcrumb_data ()
    {
        $prefix = $_MIDCOM->get_context_data($this->_contextid, MIDCOM_CONTEXT_ANCHORPREFIX);
        $result = Array();

        $curr_leaf = $this->get_current_leaf();
        $curr_node = $this->get_current_node();
        $root_node = $this->get_root_node();

        $node = $this->get_node($curr_node);
        if ($curr_leaf === false)
        {
            $leaf = null;
        }
        else
        {
            $leaf = $this->get_leaf($curr_leaf);

            // Ignore Index Article Leaves
            if ($leaf[MIDCOM_NAV_URL] == '')
            {
                $leaf = null;
            }
        }

        // Build a list of nodes, this will miss the currently active topic, it
        // is in curr_node anyway
        $nodes = Array();
        $nodeid = $curr_node;
        while ($nodeid != -1)
        {
            array_unshift($nodes, $nodeid);
            $nodeid = $this->get_node_uplink($nodeid);
            if ($nodeid == false)
            {
                break;
            }
        }

        if (! is_null($leaf))
        {
            $leaf = $this->get_leaf($curr_leaf);
            $result[$leaf[MIDCOM_NAV_ID]] = Array
            (
                MIDCOM_NAV_URL => $leaf[MIDCOM_NAV_FULLURL],
                MIDCOM_NAV_NAME => $leaf[MIDCOM_NAV_NAME],
                MIDCOM_NAV_TYPE => 'leaf',
                MIDCOM_NAV_ID => $curr_leaf,
                'napobject' => $leaf,
            );
        }

        if (! is_array($nodes))
        {
            debug_add('Warning, for some reason $nodes in get_breadcrumb_line stopped being an array. Aborting.',
                MIDCOM_LOG_ERROR);
            debug_print_r('$nodes was:', $nodes);
            return $result;
        }

        while ( count($nodes) > 0 )
        {
            $curr_node = array_pop($nodes);
            $node = $this->get_node($curr_node);
            $result[$node[MIDCOM_NAV_ID]] = Array
            (
                MIDCOM_NAV_URL => $node[MIDCOM_NAV_FULLURL],
                MIDCOM_NAV_NAME => $node[MIDCOM_NAV_NAME],
                MIDCOM_NAV_TYPE => 'node',
                MIDCOM_NAV_ID => $curr_node,
                'napobject' => $node,
            );
        }

        // We need to reverse the array now, as we added it from leaf-to-root node
        // above. (We can't use array_push if we want keys in place).
        $result = array_reverse($result, true);

        $customdata = $_MIDCOM->get_custom_context_data('midcom.helper.nav.breadcrumb');
        if (is_array($customdata))
        {
            foreach ($customdata as $key => $entry)
            {
                $id = "custom-{$key}";
                $result[$id] = Array
                (
                    MIDCOM_NAV_URL => "{$prefix}{$entry[MIDCOM_NAV_URL]}",
                    MIDCOM_NAV_NAME => $entry[MIDCOM_NAV_NAME],
                    MIDCOM_NAV_TYPE => 'custom',
                    MIDCOM_NAV_ID => $id,
                    'napobject' => $entry,
                );
            }
        }

        return $result;
    }



    /**
     * Generate a fully automated navigation.
     *
     * This method will print a simple navigation tree. It uses CSS both for line
     * formatting and for indentation. This method starts automatically at the root
     * node, the root node's leaves will not be shown.
     *
     * Here is a configuration example that can be copy&pasted, everything is mandatory:
     *
     * <code>
     * <?php
     * // These are CSS-classes, don't set them if you don't have any classes
     * // no class=... tags will be written out then
     * $config["css_div_leaf_selected"]   = "nav_leaf_selected";
     * $config["css_div_leaf_unselected"] = "nav_leaf_unselected";
     * $config["css_div_node_selected"]   = "nav_node_selected";
     * $config["css_div_node_unselected"] = "nav_node_unselected";
     * $config["css_a_leaf_unselected"] = "nav_leaf_unselected";
     * $config["css_a_node_unselected"] = "nav_node_unselected";
     *
     * // Prefix automatically prepended to any displayed leaves or nodes,
     * // leave empty if you don't want them.
     * // It must include trailing spaces if you want them between prefix and element name!
     * $config["leaf_prefix"] = "&raquo; ";
     * $config["node_prefix"] = "";
     *
     * // These are css lenght's used to build up the indentation
     * $config["indent_size"]    = "15";
     * $config["indent_linewrap"] = "5";
     * $config["indent_unit"]     = "px";
     *
     * // other Miscellaneous configuration parameters
     * // This one controls wether leaves or subnodes are displayed first
     * $config["leaves_first"] = true;
     * ?>
     * </code>
     *
     * @param Array	$config	Configuration as outlined above
     * @see midcom_helper_nav::show_combined_nav()
     */
    function show_simple_nav($config) {
        $current_node = $this->get_current_node();
        $current_leaf = $this->get_current_leaf();

        if (!array_key_exists("path",$config)) {
            $midgard = $GLOBALS["midcom"]->get_midgard();
            $config["path"] = $midgard->self;
        }

        if (!array_key_exists("level",$config))
            $config["level"] = 0;

        if (!array_key_exists("node",$config))
            $config["node"] = $this->get_root_node();

        $level = $config["level"];
        $path = $config["path"];

        echo "\n<!-- level = $level  path = $path -->\n";

        $margin_left = $level * $config["indent_size"] + $config["indent_linewrap"];

        $nodes = $this->list_nodes($config["node"]);
        if ($nodes) {
            foreach ($nodes as $node) {

                $n = $this->get_node($node);

                $title = $n[MIDCOM_NAV_NAME];
                $url = $path . $n[MIDCOM_NAV_URL];

                if (mgd_is_in_topic_tree($node, $current_node)) {

                    // Aktiv, rekursiv wiederaufrufen

                    echo "\n";
                    echo '<div style="';
                    echo "margin-left:" . $margin_left . $config["indent_unit"] . "; ";
                    echo "text-indent:-" . $config["indent_linewrap"] . $config["indent_unit"] . ";\" ";
                    if ($node == $current_node) {
                        if (array_key_exists("css_div_node_selected", $config))
                            echo 'class="' . $config["css_div_node_selected"] . '" ';
                    } else {
                        if (array_key_exists("css_div_node_unselected", $config))
                            echo 'class="' . $config["css_div_node_unselected"] . '" ';
                    }
                    echo '>';

                    if ($node == $current_node && $current_leaf === false) {
                        if (array_key_exists("node_prefix", $config))
                            echo $config["node_prefix"];
                        echo htmlspecialchars($title);
                    } else {
                        echo "<a ";
                        if (array_key_exists("css_a_node_unselected", $config))
                            echo 'class="' . $config["css_a_node_unselected"] . '" ';
                        echo 'href="' . $url . '">';
                        if (array_key_exists("node_prefix", $config))
                            echo $config["node_prefix"];
                        echo htmlspecialchars($title);
                        echo '</a>';
                    }

                    echo "</div>";

                    if (array_key_exists('leaves_first', $config ) && !$config["leaves_first"]) {
                        // Remeber that these few lines are copied below

                        $newconfig = $config;
                        $newconfig["path"] = $url;
                        $newconfig["level"]++;
                        $newconfig["node"] = $node;

                        $this->show_simple_nav($newconfig);

                        echo "\n<!-- back up to level = $level  path = $path -->\n";
                    }

                    // Articles
                    $leaves = $this->list_leaves($node);
                    foreach ($leaves as $leaf) {
                        $l = $this->get_leaf($leaf);

                        $l_title = $l[MIDCOM_NAV_NAME];
                        $l_url = $url . $l[MIDCOM_NAV_URL];

                        echo "\n";

                        if ($leaf === $this->get_current_leaf()) {
                            // Current leaf active!

                            echo '<div style="';
                            echo "margin-left:" . $margin_left . $config["indent_unit"] . "; ";
                            echo "text-indent:-" . $config["indent_linewrap"] . $config["indent_unit"] . ";" . '" ';
                            if (array_key_exists("css_div_leaf_selected", $config))
                                echo 'class="' . $config["css_div_leaf_selected"] . '" ';
                            echo '>';

                            if (array_key_exists("leaf_prefix", $config))
                                echo $config["leaf_prefix"];
                            echo htmlspecialchars($l_title);

                            echo "</div>";

                        } else {
                            // Current leaf inactive!

                            echo '<div style="';
                            echo "margin-left:" . $margin_left . $config["indent_unit"] . "; ";
                            echo "text-indent:-" . $config["indent_linewrap"] . $config["indent_unit"] . ";" . '" ';
                            if (array_key_exists("css_div_leaf_unselected", $config))
                                echo 'class="' . $config["css_div_leaf_unselected"] . '" ';
                            echo '">';

                            echo "<a ";
                            if (array_key_exists("css_div_leaf_unselected", $config))
                                echo 'class="' . $config["css_div_leaf_unselected"] . '" ';
                            echo 'href="' . $l_url . '">';
                            if (array_key_exists("leaf_prefix", $config))
                                echo $config["leaf_prefix"];
                            echo htmlspecialchars($l_title);
                            echo '</a>';

                            echo "</div>";

                        }

                    }

                    if ((array_key_exists('leaves_first', $config) && $config["leaves_first"]) ) {
                        // Remeber that these few lines are copied above

                        $newconfig = $config;
                        $newconfig["path"] = $url;
                        $newconfig["level"]++;
                        $newconfig["node"] = $node;

                        $this->show_simple_nav($newconfig);

                        echo "\n<!-- back up to level = $level  path = $path -->\n";
                    }

                } else {

                    // Normale Anzeige
                    echo "\n";
                    echo '<div style="';
                    echo "margin-left:" . $margin_left . $config["indent_unit"] . "; ";
                    echo "text-indent:-" . $config["indent_linewrap"] . $config["indent_unit"] . "; ";
                    echo '">';

                    echo "<a ";
                    if ($node == $current_node) {
                        if (array_key_exists("css_div_node_selected", $config))
                            echo 'class="' . $config["css_div_node_selected"] . '" ';
                    } else {
                        if (array_key_exists("css_div_node_unselected", $config)) {
                            echo 'class="' . $config["css_div_node_unselected"] . '" ';
                        }
                    }
                    echo 'href="' . $url . '">';
                    if (array_key_exists("node_prefix", $config))
                        echo $config["node_prefix"];
                    echo htmlspecialchars($title) . '</a>';

                    echo "</div>";
                }
            }
        }
    }

    /**
     * Generate a fully automated navigation.
     *
     * On the same basis of config data, as show_simple_nav, this function builds
     * a navigation that does not make a structural difference between nodes and
     * leaves, only in markup.  It uses the list_child_elements() method of NAP and
     * thus honors topic's user-defined NAVORDER settings.
     *
     * @param Array $config Configuration data.
     * @see midcom_helper_nav::show_simple_nav()
     */
    function show_combined_nav($config) {
        $current_node = $this->get_current_node();

        $current_leaf = $this->get_current_leaf();

        if (!array_key_exists("path",$config)) {
            $midgard = $GLOBALS["midcom"]->get_midgard();
            $config["path"] = $midgard->self;
        }

        if (!array_key_exists("level",$config))
            $config["level"] = 0;

        if (!array_key_exists("node",$config))
            $config["node"] = $this->get_root_node();

        $level = $config["level"];
        $path = $config["path"];
        $url = $path;

        echo "\n<!-- level = $level  path = $path -->\n";

        $margin_left = $level * $config["indent_size"] + $config["indent_linewrap"];

        $elements = $this->list_child_elements($config["node"]);
        if ($elements) foreach ($elements as $order => $element) {

            if ($element[MIDCOM_NAV_TYPE] === "node") {

                $n = $this->get_node($element[MIDCOM_NAV_ID]);

                $title = $n[MIDCOM_NAV_NAME];
                $url = $path . $n[MIDCOM_NAV_URL];

                if (mgd_is_in_topic_tree($element[MIDCOM_NAV_ID], $current_node)) {

                    // Current element is part of the active node's tree or the active node itself

                    echo "\n";
                    echo '<div style="';
                    echo "margin-left:" . $margin_left . $config["indent_unit"] . "; ";
                    echo "text-indent:-" . $config["indent_linewrap"] . $config["indent_unit"] . ';" ';

                    if ($element[MIDCOM_NAV_ID] == $current_node) {
                        if (array_key_exists("css_div_node_selected", $config)) {
                            echo 'class="' . $config["css_div_node_selected"] . '" ';
                            }
                    } else {
                        if (array_key_exists("css_div_node_unselected", $config)) {
                            echo 'class="' . $config["css_div_node_unselected"] . '" ';
                            } else echo "class='tov'";
                    }
                    echo '">';

                    if ($element[MIDCOM_NAV_ID] == $current_node && $current_leaf === false) {
                        if (array_key_exists("node_prefix", $config)) {
                            echo $config["node_prefix"];
                            }
                        echo htmlspecialchars($title);
                    } else {
                        echo "<a ";
                        if (array_key_exists("css_a_node_unselected", $config)) {
                            echo 'class="' . $config["css_a_node_unselected"] . '" ';
                            }
                        echo 'href="' . $url . '">';
                        if (array_key_exists("node_prefix", $config)) {
                            echo $config["node_prefix"];
                            }
                        echo htmlspecialchars($title);
                        echo '</a>';
                    }

                    echo "</div>";

                    // Call show_combined_nav() recursively for the current element's children

                    $newconfig = $config;
                    $newconfig["path"] = $url;
                    $newconfig["level"]++;
                    $newconfig["node"] = $element[MIDCOM_NAV_ID];

                    $this->show_combined_nav($newconfig);

                    echo "\n<!-- back up to level = $level  path = $path -->\n";


                } else {

                    // Current element is outside the active node's tree

                    echo "\n";
                    echo '<div style="';
                    echo "margin-left:" . $margin_left . $config["indent_unit"] . "; ";
                    echo "text-indent:-" . $config["indent_linewrap"] . $config["indent_unit"] . "; ";
                    echo '">';

                    echo "<a ";
                    if ($element[MIDCOM_NAV_ID] == $current_node) {
                        if (array_key_exists("css_node_selected", $config))
                            echo 'class="' . $config["css_node_selected"] . '" ';
                    } else {
                        if (array_key_exists("css_node_unselected", $config))
                            echo 'class="' . $config["css_node_unselected"] . '" ';
                    }
                    echo 'href="' . $url . '">';
                    if (array_key_exists("node_prefix", $config))
                        echo $config["node_prefix"];
                    echo htmlspecialchars($title) . '</a>';

                    echo "</div>";
                }
            }

            // MIDCOM_NAV_TYPE == "leaf"
            else {
                        $l = $this->get_leaf($element[MIDCOM_NAV_ID]);

                        $l_title = $l[MIDCOM_NAV_NAME];
                        $l_url = $url . $l[MIDCOM_NAV_URL];

                        echo "\n";

                        if ($element[MIDCOM_NAV_ID] === $current_leaf) {
                            // Current leaf active!

                            echo '<div style="';
                            echo "margin-left:" . $margin_left . $config["indent_unit"] . "; ";
                            echo "text-indent:-" . $config["indent_linewrap"] . $config["indent_unit"] . ";" . '" ';
                            if (array_key_exists("css_div_leaf_selected", $config))
                                echo 'class="' . $config["css_div_leaf_selected"] . '" ';
                            echo '>';

                            if (array_key_exists("leaf_prefix", $config))
                                echo $config["leaf_prefix"];
                            echo htmlspecialchars($l_title);

                            echo "</div>";

                        } else {
                            // Current leaf inactive!

                            echo '<div style="';
                            echo "margin-left:" . $margin_left . $config["indent_unit"] . "; ";
                            echo "text-indent:-" . $config["indent_linewrap"] . $config["indent_unit"] . ";" . '" ';
                            if (array_key_exists("css_div_leaf_unselected", $config))
                                echo 'class="' . $config["css_div_leaf_unselected"] . '" ';
                            echo '">';

                            echo "<a ";
                            if (array_key_exists("css_leaf_unselected", $config))
                                echo 'class="' . $config["css_leaf_unselected"] . '" ';
                            echo 'href="' . $l_url . '">';
                            if (array_key_exists("leaf_prefix", $config))
                                echo $config["leaf_prefix"];
                            echo htmlspecialchars($l_title);
                            echo '</a>';

                            echo "</div>";

                        }
            }
        }
    }

}

?>
