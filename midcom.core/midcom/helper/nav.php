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
class midcom_helper_nav
{

    /**
     * A reference to the basicnav instance in use.
     *
     * @var midcom_helper__basicnav
     * @access private
     */
    var $_basicnav;

    /**
     * The context ID we're associated with.
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
     * @param int $contextid    The id of the context you want to navigate.
     */
    function midcom_helper_nav($contextid = -1)
    {
        if ($contextid == -1)
        {
            $contextid = $_MIDCOM->get_current_context();
        }
        $this->_basicnav =& $_MIDCOM->get_basic_nav($contextid);
        $this->_contextid = $contextid;
    }


    /* The following methods are just interfaces to midcom_helper__basicnav */

    /**
     * Retrieve the ID of the currently displayed node. Defined by the topic of
     * the component that declared able to handle the request.
     *
     * @return int    The ID of the node in question.
     * @see midcom_helper__basicnav::get_current_node()
     */
    function get_current_node ()
    {
        return $this->_basicnav->get_current_node();
    }

    /**
     * Retrieve the ID of the currently displayed leaf. This is a leaf that is
     * displayed by the handling topic. If no leaf is active, this function
     * returns FALSE. (Remember to make a type sensitve check, e.g.
     * nav::get_current_leaf() !== false to distinguish '0' and 'false'.)
     *
     * @return int    The ID of the leaf in question or false on failure.
     * @see midcom_helper__basicnav::get_current_leaf()
     */
    function get_current_leaf ()
    {
        return $this->_basicnav->get_current_leaf();
    }

    /**
     * Retrieve the ID of the root node. Note that this ID is dependent from the
     * ID of the MidCOM Root topic and therefore will change as easily as the
     * root topic ID might. The MIDCOM_NAV_URL entry of the root node's data will
     * always be empty.
     *
     * @return int    The ID of the root node.
     * @see midcom_helper__basicnav::get_root_node()
     */
    function get_root_node ()
    {
        return $this->_basicnav->get_root_node();
    }

    /**
     * Lists all Sub-nodes of $parent_node. If there are no subnodes you will get
     * an empty array, if there was an error (for instance an unknown parent node
     * ID) you will get FALSE.
     *
     * @param int $parent_node    The id of the node of which the subnodes are searched.
     * @param boolean $show_noentry Show all objects on-site which have the noentry flag set.
     *     This parameter has no effect in AIS. This defaults to false.
     * @return Array            An Array of Node IDs or false on failure.
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
     * @param int $parent_node    The ID of the node of which the leaves are searched.
     * @param boolean $show_noentry Show all objects on-site which have the noentry flag set.
     *     This parameter has no effect in AIS. This defaults to false.
     * @return Array             A list of leaves found, or false on failure.
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
     * @param int $node_id    The node-id to be retrieved.
     * @return Array        The node-data as outlined in the class introduction, false on failure
     * @see midcom_helper__basicnav::get_node()
     */
    function get_node ($node_id)
    {
        return $this->_basicnav->get_node($node_id);
    }

    /**
     * This will give you a key-value pair describeing the leaf with the ID
     * $node_id. The defined keys are described above in leaf data interchange
     * format. You will get false if the leaf ID is invalid.
     *
     * @param string $leaf_id    The leaf-id to be retrieved.
     * @return Array        The leaf-data as outlined in the class introduction, false on failure
     * @see midcom_helper__basicnav::get_leaf()
     */
    function get_leaf ($leaf_id)
    {
        return $this->_basicnav->get_leaf($leaf_id);
    }

    /**
     * Returns the ID of the node to which $leaf_id is associated to, false
     * on failure.
     *
     * @param string $leaf_id    The Leaf-ID to search an uplink for.
     * @return int             The ID of the Node for which we have a match, or false on failure.
     * @see midcom_helper__basicnav::get_leaf_uplink()
     */
    function get_leaf_uplink ($leaf_id)
    {
        return $this->_basicnav->get_leaf_uplink($leaf_id);
    }

    /**
     * Returns the ID of the node to which $node_id is associated to, false
     * on failure. The root node's uplink is -1.
     *
     * @param int $node_id    The Leaf-ID to search an uplink for.
     * @return int             The ID of the Node for which we have a match, -1 for the root node, or false on failure.
     * @see midcom_helper__basicnav::get_node_uplink()
     */
    function get_node_uplink ($node_id)
    {
        return $this->_basicnav->get_node_uplink($node_id);
    }

    /**
     * Checks if the given node is within the tree of another node.
     *
     * @param int    $node_id    The node in question.
     * @param int    $root_node    The root node to use.
     * @return boolean                True, if the node is a subnode of the root node, false otherwise.
     */
    function is_node_in_tree ($node_id, $root_id)
    {
        //$topic = new midcom_db_topic();
        //return $topic->is_in_tree($root_id, $node_id);
        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('id', '=', $node_id);
        $qb->add_constraint('up', 'INTREE', $root_id);

        if ($qb->count() > 0)
        {
            return true;
        }

        return false;
    }

    /**
     * This function returns the toolbar definition for the NAP object passed. This must
     * supersede all calls to $nap_object[MIDCOM_NAV_TOOLBAR] as this information is
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
     * - MIDCOM_NAV_TYPE => 'node'
     *
     * If there are no child elements at all the method will return an empty array,
     * in case of an error FALSE.  NOTE: This method should be quite slow, there's
     * room for improvement... :-)
     *
     * @param int $parent_node_id    The ID of the parent node.
     * @return Array                A list of found elements, or false on failure.
     */
    function list_child_elements($parent_node_id)
    {
        // Fetch nodes and leaves
        if (! is_numeric($parent_node_id))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Parameter passed is no integer: [$parent_node_id]", MIDCOM_LOG_ERROR);
            debug_print_type('Type was:', $parent_node_id);
            debug_pop();
            return false;
        }

        $parent_topic = new midcom_db_topic($parent_node_id);
        if (! $parent_topic)
        {
            return false;
        }

        $navorder = (int) $parent_topic->get_parameter('midcom.helper.nav', 'navorder');

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
        
        return $result;
    }

    /**
     * This function tries to resolve a guid into a NAP object.
     *
     * The code is optimized trying to avoid a full-scan if possible. To do this it
     * will treat topic and article guids specially: In both cases the system will
     * translate it using the topic id into a node id and scan only that part of the
     * tree non-recursively.
     *
     * A full scan of the NAP data is only done if another MidgardObject is used.
     *
     * Note: If you want to resolve a GUID you got from a Permalink, use the Permalinks
     * service within MidCOM, as it covers more objects then the NAP listings.
     *
     * @param string $guid The GUID of the object to be looked up.
     * @param boolean $node_is_sufficient if we could return a good guess of correct parent node but said node does not list the $guid in leaves return the node or try to do a full (and very expensive) NAP scan ?
     * @return mixed Either a node or leaf structure, distinguishable by MIDCOM_NAV_TYPE, or false on failure.
     * @see midcom_services_permalinks
     */
    function resolve_guid ($guid, $node_is_sufficient = false)
    {
        // First, check if the GUID is already known by basicnav:
        $cached_result = $this->_basicnav->get_loaded_object_by_guid($guid);
        if (! is_null($cached_result))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('The GUID was already known by the basicnav instance, returning the cached copy directly.', MIDCOM_LOG_INFO);
            debug_pop();
            return $cached_result;
        }

        // Fetch the object in question for a start, so that we know what to do (tm)
        // Note, that objects that cannot be resolved will still be processed using a full-scan of
        // the tree. This is, for example, used by the on-delete cache invalidation.
        $object = $_MIDCOM->dbfactory->get_object_by_guid($guid);
        if (! $object)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not load GUID {$guid}, trying to continue anyway. Last error was: " . mgd_errstr(), MIDCOM_LOG_WARN);
            debug_pop();
        }

        if (is_a($object, 'midgard_topic'))
        {
            // Ok. This topic should be within the content tree,
            // we check this and return the node if everything is ok.
            if (! $this->is_node_in_tree($object->id, $this->get_root_node()))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("NAP::resolve_guid: The Guid {$guid} leads to an unknown topic not in our tree.", MIDCOM_LOG_WARN);
                debug_pop();
                return false;
            }
            return $this->get_node($object->id);
        }

        if (is_a($object, 'midgard_article'))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            // Ok, let's try to find the article using the topic in the tree.
            if (! $this->is_node_in_tree($object->topic, $this->get_root_node()))
            {
                debug_add("NAP::resolve_guid: The Guid {$guid} leads to an unknown topic not in our tree.", MIDCOM_LOG_WARN);
                debug_pop();
                return false;
            }

            $topic = new midcom_db_topic($object->topic);
            if (! $topic)
            {
                debug_pop();
                $_MIDCOM->generate_error
                (
                    MIDCOM_ERRCRIT,
                    "Data inconsistency, the topic ID ({$object->topic}) of the article {$object->id} is invalid. "
                        . 'Last error was: ' . mgd_errstr()
                );
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
            debug_pop();
            return false;
        }

        // Ok, unfortunately, this is not an immediate topic. We try to traverse
        // upwards in the object chain to find a topic.
        debug_add('Looking for a topic to use via get_parent()');
        $topic = null;

        $parent = $object->get_parent();
        
        while ($parent)
        {
            if (is_a($parent, 'midgard_topic'))
            {
                // Verify that this topic is within the current sites tree, if it is not,
                // we ignore it. This might happen on symlink topics with taviewer & co
                // which point to the outside f.x.
                if ($this->is_node_in_tree($parent->id, $this->get_root_node()))
                {
                    $topic = $parent;
                    break;
                }
            }
            $parent = $parent->get_parent();           
        }

        if ($topic !== null)
        {
            debug_add("Found topic #{$topic->id}, searching the leaves");
            $leaves = $this->list_leaves($topic->id, true);
            foreach ($leaves as $leafid)
            {
                $leaf = $this->get_leaf($leafid);
                if ($leaf[MIDCOM_NAV_GUID] == $guid)
                {
                    debug_pop();
                    return $leaf;
                }
            }
            if ($node_is_sufficient)
            {
                debug_add("Could not find guid in leaves (maybe not listed?), but node is sufficient, returning node");
                debug_pop();
                return $this->get_node($topic->id);
            }
        }

        // this is the rest of the lot, we need to traverse everything, unfortunately.
        // First, we traverse a list of nodes to be checked on by one, avoiding a recursive
        // function call.

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

        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("We were unable to find the GUID {$guid} in the MidCOM tree even with a full scan.");
        debug_pop();
        return false;
    }


    /* The more complex interface methods starts here */

    /**
     * This function provides an interface to construct links like 'View this page'.
     *
     * It takes the currently displayed content
     * element (either a leaf or node) and constructs the respective URL relative to
     * the root of the website as passed to the function.
     *
     * @param string    $baseurl    The base url that leads to the root page of the MidCOM site.
     * @return string    The full URL to the on-site element in question, null if there is no on-site representation, false on failure
     */
    function view_current_page_url ($baseurl)
    {
        // Go upwards step by step and build together the page view URL
        // up to the root topic.
        $url = '';
        if ($this->get_current_leaf() !== false)
        {
            $leaf = $this->get_leaf($this->get_current_leaf());

            if (isset($leaf[MIDCOM_NAV_URL]))
            {
                $url = $leaf[MIDCOM_NAV_URL];
            }
            elseif (isset($leaf[MIDCOM_NAV_SITE])
                && !is_null($leaf[MIDCOM_NAV_SITE]))
            {
                $url = $leaf[MIDCOM_NAV_SITE][MIDCOM_NAV_URL];
            }
            else
            {
                return null;
            }
        }
        $node_id = $this->get_current_node();

        do
        {
            $node = $this->get_node($node_id);
            $url = $node[MIDCOM_NAV_URL] . $url;
            $node_id = $this->get_node_uplink($node_id);
            if ($node_id === false)
            {
                debug_add('get_node_uplink failed; view_this_page_url aborting.');
                return false;
            }
        }
        while($node_id != -1);

        if (substr($baseurl, -1) === '/')
        {
            return $baseurl . $url;
        }

        return "{$baseurl}/{$url}";
    }

    /**
     * Construct a breadcrumb line.
     *
     * Gives you a line like 'Start > Topic1 > Topic2 > Article' using NAP to
     * traverse upwards till the root node. $separator is inserted between the
     * pairs, $class, if non-null, will be used as CSS-class for the A-Tags.
     *
     * The parameter skip_levels indicates how much nodes should be skipped at
     * the beginning of the current path. Default is to show the complete path. A
     * value of 1 will skip the home link, 2 will skip the home link and the first
     * subtopic and so on. If a leaf or node is selected, that normally would be
     * hidden, only its name will be shown.
     *
     * @param string    $separator        The separator to use between the elements.
     * @param string    $class            If not-null, it will be assigned to all A tags.
     * @param int       $skip_levels      The number of topic levels to skip before starting to work (use this to skip 'Home' links etc.).
     * @param string    $current_class    The class that should be assigned to the currently active element.
     * @param array     $skip_guids       Array of guids that are skipped.
     * @return string    The computed breadrumb line.
     */
    function get_breadcrumb_line ($separator = ' &gt; ', $class = null, $skip_levels = 0, $current_class = null, $skip_guids = array())
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

        while (current($breadcrumb_data) !== false)
        {
            $data = current($breadcrumb_data);
            $data[MIDCOM_NAV_NAME] = htmlspecialchars($data[MIDCOM_NAV_NAME]);

            // Add the next element sensitive to the fact whether we are at the end or not.
            if (next($breadcrumb_data) === false)
            {
                if ($current_class !== null)
                {
                    $result .= "<span class=\"{$current_class}\">{$data[MIDCOM_NAV_NAME]}</span>";
                }
                else
                {
                    $result .= $data[MIDCOM_NAV_NAME];
                }
            }
            else
            {
                if (   isset($data['napobject'])
                    && isset($data['napobject'][MIDCOM_NAV_GUID])
                    && in_array($data['napobject'][MIDCOM_NAV_GUID], $skip_guids)
                   )
                {
                    continue;
                }

                $result .= "<a href=\"{$data[MIDCOM_NAV_URL]}\""
                  . (is_null($class) ? '' : " class=\"{$class}\"")
                  . ">{$data[MIDCOM_NAV_NAME]}</a>{$separator}";
            }
        }

        return $result;
    }

    /**
     * Construct source data for a breadcrumb line.
     *
     * Gives you the data needed to construct a line like
     * 'Start > Topic1 > Topic2 > Article' using NAP to
     * traverse upwards till the root node. The components custom breadcrumb
     * data is inserted at the end of the computed breadcrumb line after any
     * set NAP leaf.
     *
     * See get_breadcrumb_line for a more end-user oriented way of life.
     *
     * <b>Return Value</b>
     *
     * The breadcrumb data will be returned as a list of associative arrays each
     * containing these keys:
     *
     * - MIDCOM_NAV_URL The fully qualified URL to the node.
     * - MIDCOM_NAV_NAME The clear-text name of the node.
     * - MIDCOM_NAV_TYPE One of 'node', 'leaf', 'custom' indicating what type of entry
     *   this is.
     * - MIDCOM_NAV_ID The Identifier of the structure used to build this entry, this is
     *   either a NAP node/leaf ID or the list key set by the component for custom data.
     * - 'napobject' This contains the original NAP object retrieved by the function.
     *   Just in case you need more information then is available directly.
     *
     * The entry of every level is indexed by its MIDCOM_NAV_ID, where custom keys preserve
     * their original key (as passed by the component) and prefixing it with 'custom-'. This
     * allows you to easily check if a given node/leave is within the current breadcrumb-line
     * by checking with array_key_exists. (mgd_is_in_topic_tree was originally used for this
     * purpose, but this check is not only much faster but more flexible as it isn't limited
     * to topic).
     *
     * <b>Adding custom data</b>
     *
     * Custom elements are added to this array by using the MidCOM custom component context
     * at this time. You need to add a list with the same structure as above into the
     * custom component context key <i>midcom.helper.nav.breadcrumb</i>. (This needs
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
    function get_breadcrumb_data ($id = null)
    {
        $prefix = $_MIDCOM->get_context_data($this->_contextid, MIDCOM_CONTEXT_ANCHORPREFIX);
        $result = Array();

        if (! $id)
        {
            $curr_leaf = $this->get_current_leaf();
            $curr_node = $this->get_current_node();
        }
        else
        {
            $curr_leaf = $this->get_leaf($id);
            $curr_node = -1;

            if (! $curr_leaf)
            {
                $node = $this->get_node($id);
                if ($node)
                {
                    $curr_node = $node[MIDCOM_NAV_ID];
                }
            }
            else
            {
                $curr_node = $this->get_node($curr_leaf[MIDCOM_NAV_NODEID]);
            }
        }
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

                $url = "{$prefix}{$entry[MIDCOM_NAV_URL]}";
                if (   substr($entry[MIDCOM_NAV_URL], 0, 1) == '/'
                    || preg_match('|^https?://|', $entry[MIDCOM_NAV_URL]))
                {
                    $url = $entry[MIDCOM_NAV_URL];
                }

                $result[$id] = Array
                (
                    MIDCOM_NAV_URL => $url,
                    MIDCOM_NAV_NAME => $entry[MIDCOM_NAV_NAME],
                    MIDCOM_NAV_TYPE => 'custom',
                    MIDCOM_NAV_ID => $id,
                    'napobject' => $entry,
                );
            }
        }

        return $result;
    }
}
?>