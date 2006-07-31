<?php
/**
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:_basicnav.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This class is the basic building stone of the Navigation Access Point
 * System of MidCOM.
 *
 * It is responsible for collecting the available
 * Information and for building the navigational Tree out of it. This
 * class is only the internal interface to the NAP System and is used by
 * midcom_helper_nav as a node cache. The framework should ensure that
 * only one class of this type is active at one time.
 *
 * Basicnav will give you a very abstract view of the content tree, modified
 * by the NAP classes of the components. You can retrieve a node/leaf tree
 * of the content, and for each element you can retrieve an URL name and a
 * long name for Navigation display.
 *
 * Leaves and Nodes are both indexed by Integer constants which are assigned
 * by the framework. The framework defines two starting points in this tree:
 * The root node and the "current" node. The current node defined through
 * the topic of the component that declared to be able to handle the request.
 *
 * The class will load the neccessary information on demand to minimize
 * database traffic.
 *
 * The interface functions should enable you to build any navigation tree you
 * desire. The public nav class will give you some of those high-level
 * functions.
 *
 * <b>Node data interchange format</b>
 *
 * Node NAP data consists of a simple key => value array with the following
 * keys required by the component:
 *
 * - MIDCOM_NAV_NAME => The Real (= displayable) name of the element
 * - MIDCOM_NAV_TOOLBAR => Toolbar data (see below)
 * - MIDCOM_META_CREATOR => Creator of the element (MidgardPerson)
 * - MIDCOM_META_CREATED => Creation date (UNIX Timestamp)
 * - MIDCOM_META_EDITOR  => Last modifier of the element (MidgardPerson)
 * - MIDCOM_META_EDITED  => Last modification date (UNIX Timestamp)
 *
 * Other keys delivered to NAP users include:
 *
 * - MIDCOM_NAV_URL  => The URL name of the element, which is automatically
 *   defined by NAP.
 *
 * <b>Leaf data interchaneg format</b>
 *
 * Basically for each leaf the usual meta information is returned:
 *
 * - MIDCOM_META_CREATOR => Creator of the element (MidgardPerson)
 * - MIDCOM_META_CREATED => Creation date (UNIX Timestamp)
 * - MIDCOM_META_EDITOR  => Last modifier of the element (MidgardPerson)
 * - MIDCOM_META_EDITED  => Last modification date (UNIX Timestamp)
 * - MIDCOM_NAV_GUID     => Optional argument denoting the GUID of the referred element
 * - MIDCOM_NAV_TOOLBAR  => Toolbar data (see below)
 *
 * MidCOM NAP has reduced the separation of Admin Mode and Site Mode NAP
 * Data. Both Site- and Administrative Mode data is now delivered to NAP
 * (and from there to the NAP user) in a single call. This is done by
 * creating two sub-arrays retrievable under the keys MIDCOM_NAV_SITE and
 * MIDCOM_NAV_ADMIN. Both of those arrays contain MIDCOM_NAV_URL and
 * MIDCOM_NAV_NAME values. For compatibility and ease of use the top-level
 * entries of MIDCOM_NAV_NAME and MIDCOM_NAV_URL are still existent and
 * in use. Therefore, three possible constellation exist:
 *
 * 1) Both Values present
 *
 * <pre>
 * MIDCOM_NAV_SITE => Array (MIDCOM_NAV_URL, MIDCOM_NAV_NAME),
 * MIDCOM_NAV_ADMIN => Array (MIDCOM_NAV_URL, MIDCOM_NAV_NAME),
 * [... all other tags ...]
 * </pre>
 *
 * The top-level entries MIDCOM_NAV_NAME and _URL will be automatically
 * popluated according to Admin- or non-Admin mode, so that the displaying
 * code for all standard-situations won't have to consider this difference.
 *
 * 2) One element omitted
 *
 * If any Element does have only one of those pairs (like a "Create New
 * Category"-Link for example), set the unavailable Array Entry to null:
 *
 * <pre>
 * MIDCOM_NAV_SITE => null,
 * MIDCOM_NAV_ADMIN => Array (MIDCOM_NAV_URL, MIDCOM_NAV_NAME),
 * [... all other tags ...]
 * </pre>
 *
 * Note, that if one of the elements is missing like outlined above, the
 * list-methods of NAP will not show them in the corresponding mode. The
 * element of the above example would only be included in the listings if
 * the system was running in Admin-Mode. Copying of the primary values will
 * still happen like in (1).
 *
 * 3) Compatibility syntax
 *
 * For ease-of-use and for backwards compatibility, you can deliver an old
 * style data like this:
 *
 * <pre>
 * MIDCOM_NAV_URL => "blah",
 * MIDCOM_NAV_NAME => "blubb",
 * [... all other tags ...]
 * </pre>
 *
 * The Datamanger will automatically transform (3) to the syntax described in
 * (1) by copying the values.
 *
 * <b>Important note:</b> The difference outlined above is only valid for leaves (read
 * "articles"), because the topic structure is essentially the same in both AIS
 * and live Site.
 *
 * <b>Toolbar Syntax</b>
 *
 * You can add toolbars to your NAP information, that can be used for simple on-site
 * editing. They are indexed using integers and consist of an
 * midcom_helper_toolbar::add_item() compatible array with one exception:
 * The URL is always realtive to the AIS topic welcome page, but see the example
 * (it assumes that the referenced l10n libraries are available, of course):
 *
 * <code>
 * $toolbar[0] = Array (
 *     MIDCOM_TOOLBAR_URL =&gt; '',
 *     MIDCOM_TOOLBAR_LABEL =&gt; $this-&gt;_l10n-&gt;get('create article'),
 *     MIDCOM_TOOLBAR_HELPTEXT =&gt; null,
 *     MIDCOM_TOOLBAR_ICON =&gt; 'stock-icons/16x16/stock_new.png',
 *     MIDCOM_TOOLBAR_ENABLED =&gt; true
 * );
 * $toolbar[100] = Array(
 *     MIDCOM_TOOLBAR_URL =&gt; 'config.html',
 *     MIDCOM_TOOLBAR_LABEL =&gt; $this-&gt;_l10n_midcom-&gt;get('component configuration'),
 *     MIDCOM_TOOLBAR_HELPTEXT =&gt; $this-&gt;_l10n_midcom-&gt;get('component configuration helptext'),
 *     MIDCOM_TOOLBAR_ICON =&gt; 'stock-icons/16x16/stock_folder-properties.png',
 *     MIDCOM_TOOLBAR_ENABLED =&gt; true
 * );
 * </code>
 *
 * You can now use a similar in your leaf data and place further buttons between these two
 * using indexes like 50,51,52.
 *
 * <b>DEPRECATED INFORMATION</b>
 *
 * The key MIDCOM_NAV_VISIBLE is deprecated from MidCOM 2.4.0 on, visibility is taken into account
 * automatically. The key is set to true for all values now for backwards compatibility and will
 * be removed entirely in MidCOM 2.6.0
 *
 * @todo Bring the information from http://www.nathan-syntronics.de/midgard/midcom_fs-transition/nap-update.html somehow into this.
 * @package midcom
 */
class midcom_helper__basicnav
{
    /**#@+
     * NAP data variable.
     *
     * @access private
     */

    /**
     * The GUID of the MidCOM Root Content Topic
     *
     * @var int
     */
    var $_root;

    /**
     * The GUID of the currently active Navigation Node, determied by the active
     * MidCOM Topic or one of its uplinks, if the subtree in question is invisible.
     *
     * @var int
     */
    var $_current;

    /**
     * The GUID of the currently active leaf.
     *
     * @var int
     */
    var $_currentleaf;

    /**
     * This is the leaf cache. It is an array which contains elements indexed by
     * their leaf ID. The data is again stored in an accociative array:
     *
     * - MIDCOM_NAV_NODEID => ID of the parent node (int)
     * - MIDCOM_NAV_URL => URL name of the leaf (string)
     * - MIDCOM_NAV_NAME => Textual name of the leaf (string)
     * - MIDCOM_META_CREATOR => Creator of the element (MidgardPerson)
     * - MIDCOM_META_CREATED => Creation date (UNIX Timestamp)
     * - MIDCOM_META_EDITOR  => Last modifier of the element (MidgardPerson)
     * - MIDCOM_META_EDITED  => Last modification date (UNIX Timestamp)
     *
     * @todo Update the data structure documentation
     * @var Array
     */
    var $_leaves;

    /**
     * This is the node cache. It is an array which contains elements indexed by
     * their node ID. The data is again stored in an accociative array:
     *
     * - MIDCOM_NAV_NODEID => ID of the parent node (-1 for the root node) (int)
     * - MIDCOM_NAV_URL => URL name of the leaf (string)
     * - MIDCOM_NAV_NAME => Textual name of the leaf (string)
     * - MIDCOM_META_CREATOR => Creator of the element (MidgardPerson)
     * - MIDCOM_META_CREATED => Creation date (UNIX Timestamp)
     * - MIDCOM_META_EDITOR  => Last modifier of the element (MidgardPerson)
     * - MIDCOM_META_EDITED  => Last modification date (UNIX Timestamp)
     *
     * @todo Update the data structure documentation
     * @var Array
     */
    var $_nodes;

    /**
     * This map tracks all loaded GUIDs along with their NAP structures. This cache
     * is used by nav's resolve_guid function to short-circut already known GUIDs.
     *
     * @var Array
     */
    var $_guid_map = Array();

    /**
     * This array holds a list of all topics for which the leaves have been loaded.
     * If the id of the node is in this array, the leaves are available, otheriwise,
     * the leaves ahve to be loaded.
     *
     * @var Array
     */
    var $_loaded_leaves = Array();

    /**#@-*/

    /**#@+
     * Internal runtime state variable.
     *
     * @access private
     */

    /**
     * This is a reference to the systemwide component loader class.
     *
     * @var midcom_helper__componentloader
     */
    var $_loader;

    /**
     * This one is true, if the system is in content administration mode and therefore
     * working with a different topic. See Constructor Documentation for details.
     *
     * @var bool
     */
    var $_adminmode;

    /**
     * This is a temporary storage where _loadNode can return the last known good
     * node in case the current node not visible. It is evaluated by the
     * constructor.
     *
     * @var int
     */
    var $_lastgoodnode;

    /**
     * A reference to the NAP cache store
     *
     * @var midcom_services_cache_backend
     */
    var $_nap_cache = null;

    /**#@-*/


    /**
     * This helper object will construct a complete node data structure for a given topic,
     * without any dependant objects like subtopics or leaves. It does not do any visibility
     * checks, it just prepares the object for later processing.
     *
     * This code is NAP cache aware, if the resulting information is already in the NAP
     * cache, it is retrieved from there.
     *
     * @param int $id The ID of the topic for which the NAP information is requested.
     * @return Array NAP node data structure or NULL in case no NAP information is available for this topic.
     * @access private
     */
    function _get_node($id)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        /*
        $this->_nap_cache->open();
        if ($this->_nap_cache->exists($id))
        {
            debug_add("Cache hit for the guid {$id}.");
            $nodedata = $this->_nap_cache->get($id);
            $this->_nap_cache->close();
        }
        else
        {
            debug_add("No cache hit for the guid {$id}.");
            $this->_nap_cache->close();

            $nodedata = $this->_get_node_from_database($id);
            $this->_nap_cache->put($id, $nodedata);
            debug_add("Added the guid {$id} to the cache.");
        }
        */
        // Cache disabled, see #252
        $nodedata = $this->_get_node_from_database($id);

        if (is_null($nodedata))
        {
            debug_add('We got NULL for this node, so we do not have any NAP information, returning null directly.');
            debug_pop();
            return null;
        }

        // Rewrite all host dependant URLs based on the relative URL within our topic tree.
        $nodedata[MIDCOM_NAV_FULLURL] = "{$GLOBALS['midcom_config']['midcom_site_url']}{$nodedata[MIDCOM_NAV_RELATIVEURL]}";
        $nodedata[MIDCOM_NAV_ABSOLUTEURL] = substr($GLOBALS['midcom_config']['midcom_site_url'], strlen($GLOBALS['midcom']->get_host_name()))
            . "{$nodedata[MIDCOM_NAV_RELATIVEURL]}";
        $nodedata[MIDCOM_NAV_PERMALINK] = $_MIDCOM->permalinks->create_permalink($nodedata[MIDCOM_NAV_GUID]);

        // In addition, kill the toolbar as this is cached information and thus not relevant for the current user.
        $nodedata[MIDCOM_NAV_TOOLBAR] = null;

        debug_pop();
        return $nodedata;
    }

    /**
     * Reads a node data structure from the database, completes all defaults and
     * derived properties (like ViewerGroups).
     *
     * @param int $id The ID of the topic for which the NAP information is requested.
     * @return Array Node data structure or NULL in case no NAP information is available for this topic.
     * @access private
     */
    function _get_node_from_database($id)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        // Load the topic first.
        $topic = new midcom_db_topic($id);
        if (! $topic)
        {
            /*
            // HOTFIX
            // this is most probably an access denied error. don't bail here therefor.
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                "Cannot load NAP information, aborting: Could not load the topic {$id} from the database ("
                . mgd_errstr() . ').');
            // This will exit().
            */
            debug_add("Could not load the topic {$id} through DBA; assuming missing privileges.", MIDCOM_LOG_INFO);
            debug_pop();
            return null;
        }

        debug_add("Trying to load NAP data for topic {$topic->name} (#{$topic->id})");

        // Retrieve a NAP instance

        $path = $topic->parameter('midcom', 'component');
        if ($path === false)
        {
            debug_add("The topic {$topic->id} has no component assigned to it, cannot add it to the NAP list.",
                MIDCOM_LOG_ERROR);
            return null;
        }
        if (! array_key_exists($path, $_MIDCOM->componentloader->manifests))
        {
            debug_add("The component '{$path}' of topic {$topic->id} is unknown to MidCOM, cannot add it to the NAP list.",
                MIDCOM_LOG_ERROR);
            return null;
        }
        $nap =& $this->_loader->get_nap_class($path);
        if (! $nap->set_object($topic))
        {
            debug_add("Could not set the NAP instance of '{$path}' to the topic {$topic->id}, cannot add it to the NAP list.",
                MIDCOM_LOG_ERROR);
            return null;
        }

        // Get the node data and verify this is a node that actually has any relevant NAP
        // information. Internal components like AIS or the L10n editor, which don't have
        // a NAP interface yet return null here, to be exempt from any NAP processing.
        $nodedata = $nap->get_node();
        if (is_null($nodedata))
        {
            debug_add("The component '{$path}' did return null for the topic {$topic->id}, indicating no NAP information is available.");
            debug_pop();
            return null;
        }

        // Now complete the node data structure, we need a metadata object for this:
        $metadata =& midcom_helper_metadata::retrieve($topic);

        $nodedata[MIDCOM_NAV_URL] = $topic->name . '/';
        $nodedata[MIDCOM_NAV_NAME] = trim($nodedata[MIDCOM_NAV_NAME]) == '' ? $topic->name : $nodedata[MIDCOM_NAV_NAME];
        $nodedata[MIDCOM_NAV_GUID] = $topic->guid();
        $nodedata[MIDCOM_NAV_ID] = $topic->id;
        $nodedata[MIDCOM_NAV_TYPE] = 'node';
        $nodedata[MIDCOM_NAV_SCORE] = $topic->score;
        $nodedata[MIDCOM_NAV_COMPONENT] = $path;
        $nodedata[MIDCOM_NAV_SUBNODES] = null;
        $nodedata[MIDCOM_NAV_LEAVES] = null;

        if (!array_key_exists(MIDCOM_NAV_ICON, $nodedata)) {
            $nodedata[MIDCOM_NAV_ICON] = null;
        }

        if (! array_key_exists(MIDCOM_NAV_TOOLBAR, $nodedata))
        {
            $nodedata[MIDCOM_NAV_TOOLBAR] = null;
        }

        if (! array_key_exists(MIDCOM_NAV_CONFIGURATION, $nodedata))
        {
            $nodedata[MIDCOM_NAV_CONFIGURATION] = null;
        }

        if (   ! array_key_exists(MIDCOM_NAV_NOENTRY, $nodedata)
            || $nodedata[MIDCOM_NAV_NOENTRY] == false)
        {
            $nodedata[MIDCOM_NAV_NOENTRY] = (bool) $metadata->get('nav_noentry');
        }

        if ($topic->id == $this->_root)
        {
            $nodedata[MIDCOM_NAV_NODEID] = -1;
            $nodedata[MIDCOM_NAV_RELATIVEURL] = '';
        }
        else
        {
            $nodedata[MIDCOM_NAV_NODEID] = $topic->up;
            $nodedata[MIDCOM_NAV_RELATIVEURL] = $this->_nodes[$nodedata[MIDCOM_NAV_NODEID]][MIDCOM_NAV_RELATIVEURL] . $nodedata[MIDCOM_NAV_URL];
        }
        $nodedata[MIDCOM_NAV_OBJECT] = $topic;

        // Collect Viewergroups
        // A topic can be viewed by all groups by default
        $nodedata[MIDCOM_NAV_VIEWERGROUPS] = null;

        /*
        $topic_viewers = $topic->listparameters("ViewerGroups");
        if ($topic_viewers)
        {
            $groups = Array();
            while ($topic_viewers->fetch())
            {
                if ($topic_viewers->name == 'all')
                {
                    // There is an "all" value, everybody can access this topic for sure.
                    break;
                }
                $groups[] = $topic_viewers->name;            }
            $nodedata[MIDCOM_NAV_VIEWERGROUPS] = $groups;
        }
        */

        // Temporary compatiblity value
        $nodedata[MIDCOM_NAV_VISIBLE] = true;

        debug_pop();
        return $nodedata;
    }

    /**
     * Return the list of leaves for a given node. This helper will construct complete leaf
     * data structures for each leaf found. It will first check the cache for the leaf structures,
     * and query the database only if the corresponding objects have not been found there.
     *
     * No visibility checks are made at this point.
     *
     * @param Array $node The node data structure for which to retrieve the leaves.
     * @return Array All leaves found for that node, in complete post processed leave data structures.
     * @access private
     */
    function _get_leaves($node)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Trying to load NAP leaf data for topic {$node[MIDCOM_NAV_OBJECT]->name} (#{$node[MIDCOM_NAV_OBJECT]->id})");

        $entry_name = "{$node[MIDCOM_NAV_ID]}-leaves";

        /*
        $this->_nap_cache->open();
        if ($this->_nap_cache->exists($entry_name))
        {
            $leaves = $this->_nap_cache->get($entry_name);
        }
        else
        {
            $leaves = null;
        }
        $this->_nap_cache->close();
        */
        // Cache disabled until #252 is resolved
        $leaves = null;

        if (is_null($leaves))
        {
            // Appearantly, the leaves have not yet been loaded for this topic, so we have to do this now.
            // Afterwards we update the cache.
            debug_add('The leaves have not yet been loaded from the database, we do this now.');

            $leaves = $this->_get_leaves_from_database($node);

            // Cache disabled until #252 is resolved
            // $this->_write_leaves_to_cache($node, $leaves);
        }

        // Post process the leaves for URLs and the like. Don't forget NAV_ADMIN/NAV_SITE
        // Rewrite all host dependant URLs based on the relative URL within our topic tree.
        $this->_update_leaflist_urls($leaves);

        // Don't log, this can get really big.
        // debug_print_r("We will return these leaves:", $leaves);
        debug_pop();
        return $leaves;
    }

    /**
     * This helper updates the URLs in the reference-passed leaf list.
     * FULLURL, ABSOLUTEURL and PERMALINK are built upon RELATIVEURL,
     * NAV_NAME and NAV_URL are populated based on the administration mode with either
     * NAV_ADMIN or NAV_SITE values.
     *
     * @param Array $leaves A reference to the list of leaves which has to be processed.
     * @access private
     */
    function _update_leaflist_urls(&$leaves)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $fullprefix = "{$GLOBALS['midcom_config']['midcom_site_url']}";
        $absoluteprefix = substr($GLOBALS['midcom_config']['midcom_site_url'], strlen($GLOBALS['midcom']->get_host_name()));

        if (! is_array($leaves))
        {
            echo "<pre>"; print_r($leaves); echo "</pre>";
            die ("wrong type");
        }

        foreach ($leaves as $id => $copy)
        {
            $leaves[$id][MIDCOM_NAV_FULLURL] = $fullprefix . $leaves[$id][MIDCOM_NAV_RELATIVEURL];
            $leaves[$id][MIDCOM_NAV_ABSOLUTEURL] = $absoluteprefix . $leaves[$id][MIDCOM_NAV_RELATIVEURL];
            if (is_null($leaves[$id][MIDCOM_NAV_GUID]))
            {
                $leaves[$id][MIDCOM_NAV_PERMALINK] = $leaves[$id][MIDCOM_NAV_FULLURL];
            }
            else
            {
                $leaves[$id][MIDCOM_NAV_PERMALINK] = $_MIDCOM->permalinks->create_permalink($leaves[$id][MIDCOM_NAV_GUID]);
            }
            if ($this->_adminmode)
            {
                $leaves[$id][MIDCOM_NAV_URL] = $leaves[$id][MIDCOM_NAV_ADMIN][MIDCOM_NAV_URL];
                $leaves[$id][MIDCOM_NAV_NAME] = $leaves[$id][MIDCOM_NAV_ADMIN][MIDCOM_NAV_NAME];
            }
            else
            {
                $leaves[$id][MIDCOM_NAV_URL] = $leaves[$id][MIDCOM_NAV_SITE][MIDCOM_NAV_URL];
                $leaves[$id][MIDCOM_NAV_NAME] = $leaves[$id][MIDCOM_NAV_SITE][MIDCOM_NAV_NAME];
            }

            // In addition, kill the toolbar as this is cached information and thus not relevant for the current user.
            $leaves[$id][MIDCOM_NAV_TOOLBAR] = null;
        }

        debug_pop();
    }

    /**
     * Writes the leaves passed to this function to the cache, assigning them to the
     * specified node.
     *
     * The function will bail out on any critical error. Data inconsistencies will be
     * logged and overwritten silently otherwise.
     *
     * @param Array $node The node datastructure to which the leaves should be assigned.
     * @param Array $leaves The leaves to store in the cache.
     * @access private
     */
    function _write_leaves_to_cache($node, $leaves)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('Writing ' . count ($leaves) . ' leaves to the cache.');

        // We need to update the node too, as it contains the leaf list for rapid access.
        $node_leaflist = array_keys($leaves);

        $this->_nap_cache->open(true);

        if (! $this->_nap_cache->exists($node[MIDCOM_NAV_ID]))
        {
            $this->_nap_cache->close();
            debug_add("NAP Caching Engine: Tried to update the topic {$node[MIDCOM_NAV_OBJECT]->name} (#{$node[MIDCOM_NAV_OBJECT]->id}) "
                . 'which was supposed to be in the cache already, but failed to load the object from the database. '
                . 'Aborting write_to_cache, this is a critical cache inconsistency.', MIDCOM_LOG_WARN);
            debug_pop();
            return;
        }

        // We load it again to get the cached structure, not the completed one from the
        // in-memory cache.
        $cached_node = $this->_nap_cache->get($node[MIDCOM_NAV_ID]);
        $cached_node[MIDCOM_NAV_LEAVES] = $node_leaflist;

        debug_print_r('Updating the Node structure in the cache to this:', $cached_node);
        $this->_nap_cache->put($node[MIDCOM_NAV_ID], $cached_node);

        $this->_nap_cache->put("{$node[MIDCOM_NAV_ID]}-leaves", $leaves);

        $this->_nap_cache->close();
        debug_pop();
    }

    /**
     * This helper is responsible for loading the leaves for a given node out of the
     * database. It will complete all default fields to provide full blown nap structures.
     * It will also build the base relative URLs which will later be completed by the
     * _get_leaves() interface functions.
     *
     * Important notes:
     * - The ViewerGroups property is copied from the parent topic, to ensure the same level of visibility.
     * - The IDs constructed for the leaves are the concatenation of the ID delivered by the component
     *   and the topics' GUID.
     *
     * @param Array $node The node data structure for which to retrieve the leaves.
     * @return Array All leaves found for that node, in complete post processed leave data structures.
     * @access private
     */
    function _get_leaves_from_database($node)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $topic = $node[MIDCOM_NAV_OBJECT];

        // Retrieve a NAP instance
        $nap =& $this->_loader->get_nap_class($node[MIDCOM_NAV_COMPONENT]);
        if (! $nap->set_object($topic))
        {
            debug_print_r('Topic object dump:', $topic);
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                "Cannot load NAP information, aborting: Could not set the nap instance of {$path} to the topic {$topic->id}.");
            // This will exit().
        }

        $leafdata = $nap->get_leaves();
        $leaves = Array();

        foreach ($leafdata as $id => $leaf)
        {
            // First, try to somehow gain both a GUID and a Leaf.
            if (   ! array_key_exists(MIDCOM_NAV_GUID, $leaf)
                && ! array_key_exists(MIDCOM_NAV_OBJECT, $leaf))
            {
                debug_add("Warning: The leaf {$id} of topic {$topic->id} does set neither a GUID nor an object.", MIDCOM_LOG_WARN);
                $leaf[MIDCOM_NAV_GUID] = null;
                $leaf[MIDCOM_NAV_OBJECT] = null;
            }
            else if (! array_key_exists(MIDCOM_NAV_GUID, $leaf))
            {
                $leaf[MIDCOM_NAV_GUID] = $leaf[MIDCOM_NAV_OBJECT]->guid();
            }
            else if (! array_key_exists(MIDCOM_NAV_OBJECT, $leaf))
            {
                $leaf[MIDCOM_NAV_OBJECT] = mgd_get_object_by_guid($leaf[MIDCOM_NAV_GUID]);
            }
            $leaf[MIDCOM_NAV_VIEWERGROUPS] = $node[MIDCOM_NAV_VIEWERGROUPS];

            // Now complete the actual leaf information

            // Score
            if (! array_key_exists(MIDCOM_NAV_SCORE, $leaf))
            {
                if (   $leaf[MIDCOM_NAV_OBJECT]
                    && array_key_exists('score', $leaf[MIDCOM_NAV_OBJECT]))
                {
                    $leaf[MIDCOM_NAV_SCORE] = $leaf[MIDCOM_NAV_OBJECT]->score;
                }
                else
                {
                    $leaf[MIDCOM_NAV_SCORE] = 0;
                }
            }

            // NAV_NOENTRY Flag
            if (! array_key_exists(MIDCOM_NAV_NOENTRY, $leaf))
            {
                $leaf[MIDCOM_NAV_NOENTRY] = false;
            }
            if ($leaf[MIDCOM_NAV_NOENTRY] == false)
            {
                $metadata =& midcom_helper_metadata::retrieve($leaf);
                if ($metadata)
                {
                    $leaf[MIDCOM_NAV_NOENTRY] = (bool) $metadata->get('nav_noentry');
                }
            }

            // Complete the NAV_SITE and NAV_ADMIN fields if the old-style
            // root level URL/NAME parameters are set. This automatically overrides
            // any NAV_SITE/ADMIN settings.
            if (   array_key_exists(MIDCOM_NAV_NAME, $leaf)
                && array_key_exists(MIDCOM_NAV_URL, $leaf))
            {
                $leaf[MIDCOM_NAV_SITE][MIDCOM_NAV_URL] = $leaf[MIDCOM_NAV_URL];
                $leaf[MIDCOM_NAV_SITE][MIDCOM_NAV_NAME] = $leaf[MIDCOM_NAV_NAME];
                $leaf[MIDCOM_NAV_ADMIN][MIDCOM_NAV_URL] = $leaf[MIDCOM_NAV_URL];
                $leaf[MIDCOM_NAV_ADMIN][MIDCOM_NAV_NAME] = $leaf[MIDCOM_NAV_NAME];
            }

            // complete NAV_NAMES where neccessary
            if (   ! is_null($leaf[MIDCOM_NAV_SITE])
                && trim($leaf[MIDCOM_NAV_SITE][MIDCOM_NAV_NAME]) == '')
            {
                $leaf[MIDCOM_NAV_SITE][MIDCOM_NAV_NAME] = $leaf[MIDCOM_NAV_ADMIN][MIDCOM_NAV_URL];
            }
            if (   ! is_null($leaf[MIDCOM_NAV_ADMIN])
                && trim($leaf[MIDCOM_NAV_ADMIN][MIDCOM_NAV_NAME]) == '')
            {
                $leaf[MIDCOM_NAV_ADMIN][MIDCOM_NAV_NAME] = $leaf[MIDCOM_NAV_ADMIN][MIDCOM_NAV_URL];
            }

            // Toolbar
            if (! array_key_exists(MIDCOM_NAV_TOOLBAR, $leaf))
            {
                $leaf[MIDCOM_NAV_TOOLBAR] = null;
            }

            // Some basic information
            $leaf[MIDCOM_NAV_TYPE] = 'leaf';
            $leaf[MIDCOM_NAV_ID] = "{$node[MIDCOM_NAV_ID]}-{$id}";
            $leaf[MIDCOM_NAV_NODEID] = $node[MIDCOM_NAV_ID];
            $leaf[MIDCOM_NAV_RELATIVEURL] = $node[MIDCOM_NAV_RELATIVEURL] . $leaf[MIDCOM_NAV_SITE][MIDCOM_NAV_URL];
            if (!array_key_exists(MIDCOM_NAV_ICON, $leaf)) {
                $leaf[MIDCOM_NAV_ICON] = null;
            }

            // Save the original Leaf ID so that it is easier to query in topic-specific NAP code
            $leaf[MIDCOM_NAV_LEAFID] = $id;

            // Temporary compatibility value
            $leaf[MIDCOM_NAV_VISIBLE] = true;

            // The leaf is complete, add it.
            $leaves[$leaf[MIDCOM_NAV_ID]] = $leaf;
        }

        debug_pop();
        return $leaves;
    }

    /**
     * Checks, if the NAP object indicated by $napdata is visible within the current
     * runtime environment. It will work with both nodes and leaves.
     * This includes checks for:
     *
     * - Nonexistant NAP information (null values)
     * - Viewergroups
     * - Scheduling/Hiding (only on-site)
     * - Approval (only on-site)
     *
     * @param Array $napdata The NAP data structure for the object to check (supports NULL values).
     * @return bool Indicating visibility.
     * @access private
     * @todo Integrate with midcom_helper_metadata::is_object_visible_onsite()
     */
    function _is_object_visible($napdata)
    {
        if (is_null($napdata))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Got a null value as napdata, so this object does not have any NAP info, so we cannot display it.');
            debug_pop();
            return false;
        }

        if (! is_null($napdata[MIDCOM_NAV_VIEWERGROUPS]))
        {
            $visible = false;
            foreach ($napdata[MIDCOM_NAV_VIEWERGROUPS] as $guid)
            {
                if ($GLOBALS["midcom"]->check_memberships($guid))
                {
                    // User is member of a viewer group
                    $visible = true;
                }
            }
            if (! $visible)
            {
                return false;
            }
        }

        // If in admin mode, we are always visible now.
        if ($this->_adminmode)
        {
            return true;
        }

        // If this is a leaf, we have to do some additional AIS/Non-AIS visibility checks
        if ($napdata[MIDCOM_NAV_TYPE] == 'leaf')
        {
            // Check wether the leaf may be shown in the current mode
            if (   (  $this->_adminmode && is_null($napdata[MIDCOM_NAV_ADMIN]))
                || (! $this->_adminmode && is_null($napdata[MIDCOM_NAV_SITE])))
            {
                return false;
            }
        }

        // Check the Metadata if and only if we are configured to do so.
        if (   $GLOBALS['midcom_config']['show_hidden_objects'] == false
            || $GLOBALS['midcom_config']['show_unapproved_objects'] == false)
        {
            // Check Hiding, Scheduling and Approval
            $metadata =& midcom_helper_metadata::retrieve($napdata[MIDCOM_NAV_OBJECT]);

            if (! $metadata)
            {
                // For some reason, the metadata for this object could not be retrieved. so we skip
                // Approval/Visibility checks.
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Warning, no Metadata available for the {$napdata[MIDCOM_NAV_TYPE]} {$napdata[MIDCOM_NAV_GUID]}.", MIDCOM_LOG_INFO);
                debug_pop();
                return true;
            }

            if (! $metadata->is_object_visible_onsite())
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Load the Navigational information accociated with the topic $param, which
     * can be passed as an ID or as a MidgardTopic object. This is differentiated
     * by the flag $idmode (true for id, false for MidgardTopic).
     *
     * This method does query the topic for all information and completes it to
     * build up a full NAP data structure
     *
     * It determines the URL_NAME of the topic automatically using the name of the
     * topic in question.
     *
     * The currently active leaf is only queried if and only if the currently
     * processed topic is equal to the current context's content topic. This should
     * prevent dynamically loaded components from disrupting active leaf information,
     * as this can happen if dynamic_load is called before showing the navigation.
     *
     * @param mixed $param	Topic to be processed.
     * @param bool $idmode	$param is an integer if true, an MidgardTopic otherwise.
     * @return int			One of the MGD_ERR constants
     */
    function _loadNodeData($param, $idmode = true)
    {
        global $midcom_errstr;
        debug_push_class(__CLASS__, __FUNCTION__);

        // Load the object.
        if ($idmode)
        {
            $topic_id = $param;
            $topic = mgd_get_topic($topic_id);
            if (!$topic)
            {
                $midcom_errstr = "Could not open Topic: " . mgd_errstr();
                debug_add($midcom_errstr, MIDCOM_LOG_ERROR);
                debug_pop();
                return MIDCOM_ERRCRIT;
            }
        }
        else
        {
            $topic = $param;
            $topic_id = $topic->id;
        }

        // Load the node data and check visibility.
        $nodedata = $this->_get_node($topic->id);
        if (! $this->_is_object_visible($nodedata))
        {
            debug_pop();
            return MIDCOM_ERRFORBIDDEN;
        }
        // The node is visible, add it to the list.
        $this->_nodes[$nodedata[MIDCOM_NAV_ID]] = $nodedata;
        $this->_guid_map[$nodedata[MIDCOM_NAV_GUID]] =& $this->_nodes[$nodedata[MIDCOM_NAV_ID]];

        // Load the current leaf, this does *not* load the leaves from the DB, this is done
        // during get_leaf now.
        if ($this->_current == $topic->id)
        {
            $nap =& $this->_loader->get_nap_class($nodedata[MIDCOM_NAV_COMPONENT]);
            $currentleaf = $nap->get_current_leaf();
            if ($currentleaf !== false)
            {
                $this->_currentleaf = "{$nodedata[MIDCOM_NAV_ID]}-{$currentleaf}";
            }
        }

        debug_pop();
        return MIDCOM_ERROK;
    }

    /**
     * Loads the leaves for a given node from the cache or database.
     * It will relay the code to _get_leaves() and check the object visibility upon
     * return.
     *
     * @param Array $node The NAP node data structure to load the nodes for.
     */
    function _load_leaves($node)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (in_array($node[MIDCOM_NAV_ID], $this->_loaded_leaves))
        {
            debug_add("Warning, tried to load the leaves of noe {$node[MIDCOM_NAV_ID]} more then once.", MIDCOM_LOG_INFO);
            debug_pop();
            return;
        }

        debug_add("Loading leaves for node {$node[MIDCOM_NAV_ID]}");

        $leaves = $this->_get_leaves($node);
        foreach ($leaves as $id => $leaf)
        {
            if ($this->_is_object_visible($leaf))
            {
                // The leaf is visible, add it to the list.
                $this->_leaves[$id] = $leaf;
                $this->_guid_map[$leaf[MIDCOM_NAV_GUID]] =& $this->_leaves[$id];
            }
        }
        $this->_loaded_leaves[] = $node[MIDCOM_NAV_ID];

        debug_pop();
    }

    /**
     * This function is the controlling instance of the loading mechanism. It
     * is able to load the navigation data of any topic within MidCOMs topic
     * tree into memory. Any uplink nodes that are not loaded into memory will
     * be loaded until any other known topic is encountered. After the
     * neccessary data has been loaded with calls to _loadNodeData.
     *
     * If all load calls were successful, MIDCOM_ERROK is returned. Any error
     * will be indicated with a corresponding return value and an error message
     * in $midcom_errstr.
     *
     * @param int $nodeid	The ID of the topic to be loaded
     * @return int			MIDCOM_ERROK on succes, one of the MIDCOM_ERR... constants upon an error
     * @access private
     */
    function _loadNode($node_id)
    {
        global $midcom_errstr;
        if (! is_numeric($node_id))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            $midcom_errstr = "Node $node_id is not a number. Aborting";
            debug_add($midcom_errstr, MIDCOM_LOG_WARN);
            debug_print_r("Passed node id was:", $node_id);
            debug_pop();
            return MIDCOM_ERRNOTFOUND;
        }
        $node_id = (int) $node_id;

        if (array_key_exists($node_id, $this->_nodes))
        {
            return MIDCOM_ERROK;
        }

        if (   $node_id != $this->_root
            && ! mgd_is_in_topic_tree($this->_root, $node_id))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            $midcom_errstr = "Node $node_id is not in the MidCOM content tree $this->_root. Aborting";
            debug_add($midcom_errstr, MIDCOM_LOG_ERROR);
            debug_pop();
            return MIDCOM_ERRNOTFOUND;
        }

        $topic = mgd_get_topic ($node_id);
        if (!$topic)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            $midcom_errstr = "Could not load Topic $node_id: " . mgd_errstr();
            debug_add($midcom_errstr, MIDCOM_LOG_ERROR);
            debug_pop();
            return MIDCOM_ERRCRIT;
        }

        if (!array_key_exists($topic->up, $this->_nodes) && $topic->id != $this->_root)
        {
            $toload = array();
            $uplink = $topic;

            do
            {
                $uplink = mgd_get_topic($uplink->up);
                if (!$uplink)
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    $midcom_errstr = "Could not load Topic " . $node_id . ": " . mgd_errstr();
                    debug_add($midcom_errstr, MIDCOM_LOG_ERROR);
                    debug_pop();
                    return MIDCOM_ERRCRIT;
                }
                $toload[] = $uplink->id;
            }
            while (   !array_key_exists ($uplink->up,$this->_nodes)
                   && $uplink->id != $this->_root);

            // pop each element of $toload off and call _loadNode on it.

            $uplink = ($uplink->id == $this->_root) ? -1 : $uplink->up;
            $id = array_pop($toload);

            while (!is_null($id))
            {
                $result = $this->_loadNodeData($id);

                switch ($result)
                {
                    case MIDCOM_ERRFORBIDDEN:
                        debug_push_class(__CLASS__, __FUNCTION__);
                        $midcom_errstr = "The Node $id is invisible, could not satisfy the the dependency chain to Node $node_id";
                        debug_add($midcom_errstr, MIDCOM_LOG_ERROR);
                        debug_pop();
                        return MIDCOM_ERRFORBIDDEN;

                    case MIDCOM_ERRCRIT:
                        return MIDCOM_ERRCRIT;
                }

                $this->_lastgoodnode = $id;
                $id = array_pop($toload);
            }
        }

        return $this->_loadNodeData($topic->id);
    }

    /**
     * Constructor
     *
     * The only constructor of the Basicnav class. It will initialize Root-Topic,
     * Current-Topic and all cache arrays. The function will load all nodes
     * between root and current node.
     *
     * If the current node is behind an invisible or undecendable node, the last
     * known good node will be used instead for the current node.
     *
     * The constructor retrievs all initialisation data from the component context.
     * A special process is used, if the context in question is of the type
     * MIDCOM_REQUEST_CONTENTADM: The system then goes into Administration Mode,
     * querying the components for the administrative data instead of their regular
     * data. In addition, the root topic is set to the administrated topic instead
     * of the regular root topic. This way you can build up Admin Interface
     * Navigation for "external" trees.
     *
     * @param int $context	The Context ID for which to create NAP data for, defaults to 0
     */
    function midcom_helper__basicnav($context = 0)
    {
        global $midcom;

        debug_push("_basicnav::constructor");

        $tmp = $midcom->get_context_data($context, MIDCOM_CONTEXT_ROOTTOPIC);
        $this->_root = $tmp->id;

        // $this->_nap_cache =& $midcom->cache->nap->get_nap_cache($GLOBALS['midcom_config']['midcom_root_topic_guid']);

        $this->_leaves = array();
        $this->_nodes = array();
        $this->_loader =& $midcom->get_component_loader();
        if ($midcom->get_context_data($context, MIDCOM_CONTEXT_REQUESTTYPE) == MIDCOM_REQUEST_CONTENTADM)
        {
            $this->_adminmode = true;
        }
        else
        {
            $this->_adminmode = false;
        }

        $current = $midcom->get_context_data($context, MIDCOM_CONTEXT_CONTENTTOPIC);
        if (is_null($current))
        {
            $this->_current = $this->_root;
        }
        else
        {
            $this->_current = $current->id;
        }
        $this->_currentleaf = false;

        $this->_lastgoodnode = -1;

        switch ($this->_loadNode($this->_current))
        {
            case MIDCOM_ERROK:
                break;

            case MIDCOM_ERRFORBIDDEN:
                debug_add("The current node is hidden behind a undecendable one.", MIDCOM_LOG_INFO);
                debug_add("Activating last good node ({$this->_lastgoodnode}) as current node");
                $this->_current = $this->_lastgoodnode;
                break;

            default:
                debug_add("_loadNode failed, see above error for details.", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
        }

        // Reset the Root node's URL Parameter to an empty string.

        $this->_nodes[$this->_root][MIDCOM_NAV_URL] = "";

        debug_pop();
    }

    /**
     * Retrieve the ID of the currently displayed node. Defined by the topic of
     * the component that declared able to handle the request.
     *
     * @return int	The ID of the node in question.
     */
    // Keep this doc in sync with midcom_helper_nav
    function get_current_node()
    {
        return $this->_current;
    }

    /**
     * Retrieve the ID of the currently displayed leaf. This is a leaf that is
     * displayed by the handling topic. If no leaf is active, this function
     * returns FALSE. (Remeber to make a type sensitve check, e.g.
     * nav::get_current_leaf() !== false to distinguish "0" and "false".)
     *
     * @return int	The ID of the leaf in question or false on failure.
     */
    // Keep this doc in sync with midcom_helper_nav
    function get_current_leaf()
    {
        return $this->_currentleaf;
    }

    /**
     * Retrieve the ID of the root node. Note that this ID is dependent from the
     * ID of the MidCOM Root topic and therefore will change as easily as the
     * root topic ID might. The MIDCOM_NAV_URL entry of the root node's data will
     * always be empty.
     *
     * @return int	The ID of the root node.
     */
    // Keep this doc in sync with midcom_helper_nav
    function get_root_node()
    {
        return $this->_root;
    }

    /**
     * Lists all Sub-nodes of $parent_node. If there are no subnodes you will get
     * an empty array, if there was an error (for instance an unkown parent node
     * ID) you will get FALSE.
     *
     * @param int $parent_node	The id of the node of which the subnodes are searched.
     * @param bool $show_noentry Show all objects on-site which have the noentry flag set. This parameter has no effect in AIS.
     * @return Array			An Array of Node IDs or false on failure.
     */
    // Keep this doc in sync with midcom_helper_nav
    function list_nodes($parent_node, $show_noentry)
    {
        global $midcom_errstr;

        debug_push("_basicnav::list_nodes");

        if (! is_numeric($parent_node))
        {
            debug_add("Parameter passed is no integer: [$parent_node]", MIDCOM_LOG_ERROR);
            debug_print_type('Type was:', $parent_node);
            debug_pop();
            return false;
        }

        if (!array_key_exists($parent_node, $this->_nodes))
        {
            if ($this->_loadNode($parent_node) != MIDCOM_ERROK)
            {
                debug_add("Unable to load parent node $parent_node", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
        }

        $qb = $_MIDCOM->dbfactory->new_query_builder('midcom_db_topic');
        $qb->add_constraint('up', '=', $parent_node);
        $qb->add_order('score');
        $qb->add_order('extra');
        $query_result = $_MIDCOM->dbfactory->exec_query_builder($qb);

        if ($query_result === false)
        {
            $midcom_errstr = "Could not list topics: " . mgd_errstr();
            debug_add($midcom_errstr, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $result = array();
        foreach ($query_result as $topic)
        {
            if ($this->_loadNode($topic->id) != MIDCOM_ERROK)
            {
                debug_add("Node {$topic->id} could not be loaded, ignoring it", MIDCOM_LOG_INFO);
                continue;
            }
            if ($show_noentry || ! $this->_nodes[$topic->id][MIDCOM_NAV_NOENTRY])
            {
                $result[] = $topic->id;
            }
        }
        debug_pop();
        return $result;
    }

    /**
     * Lists all leaves of $parent_node. If there are no leaves you will get an
     * empty array, if there was an error (for instance an unknown parent node ID)
     * you will get FALSE.
     *
     * @param int $parent_node	The ID of the node of which the leaves are searched.
     * @param bool $show_noentry Show all objects on-site which have the noentry flag set. This parameter has no effect in AIS.
     * @return Array 			A list of leaves found, or false on failure.
     */
    // Keep this doc in sync with midcom_helper_nav
    function list_leaves ($parent_node, $show_noentry)
    {
        debug_push ("_basicnav::list_leaves");

        if (! is_numeric($parent_node))
        {
            debug_add("Parameter passed is no integer: [$parent_node]", MIDCOM_LOG_ERROR);
            debug_print_type('Type was:', $parent_node);
            debug_pop();
            return false;
        }

        if (!array_key_exists($parent_node, $this->_nodes))
        {
            if ($this->_loadNode($parent_node) != MIDCOM_ERROK)
            {
                debug_pop();
                return false;
            }
        }
        if (! in_array($parent_node, $this->_loaded_leaves))
        {
            $this->_load_leaves($this->_nodes[$parent_node]);
        }

        $result = array();
        foreach ($this->_leaves as $id => $leaf)
        {
            if (   $leaf[MIDCOM_NAV_NODEID] == $parent_node
                && ($show_noentry || ! $leaf[MIDCOM_NAV_NOENTRY]))
            {
                $result[] = $id;
            }
        }

        debug_pop();
        return $result;
    }

    /**
     * This will give you a key-value pair describeing the node with the ID
     * $node_id. The defined keys are described above in Node data interchange
     * format. You will get false if the node ID is invalid.
     *
     * @param int $node_id	The node-id to be retrieved.
     * @return Array		The node-data as outlined in the class introduction, false on failure
     */
    // Keep this doc in sync with midcom_helper_nav
    function get_node ($node_id)
    {
        if (! is_numeric($node_id))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Parameter passed is no integer: [$node_id]", MIDCOM_LOG_ERROR);
            debug_print_type('Type was:', $node_id);
            debug_pop();
            return false;
        }

        if (!array_key_exists($node_id, $this->_nodes))
        {
            if ($this->_loadNode($node_id) != MIDCOM_ERROK)
            {
                return false;
            }
        }

        return $this->_nodes[$node_id];
    }

    /**
     * Verifies the existence of a given leaf. Call this before getting a leaf from the
     * $_leaves cache. It will load all neccessary nodes/leaves as neccessary.
     *
     * @param string $leaf_id A valid NAP leaf id ($nodeid-$leafid pattern).
     * @return bool true if the leaf exists, false otherwise.
     */
    function _check_leaf_id($leaf_id)
    {
         if (! $leaf_id)
         {
            debug_add("Tried to load a suspicious leaf id, probably a FALSE from get_current_leaf.");
            return false;
        }

        if (array_key_exists($leaf_id, $this->_leaves))
        {
            return true;
        }

        $id_elements = explode('-', $leaf_id);

        $node_id = $id_elements[0];

        if (   ! array_key_exists($node_id, $this->_nodes)
            && $this->_loadNode($node_id) != MIDCOM_ERROK)
        {
            debug_add("Tried to verify the leaf id {$leaf_id}, which should belong to node {$node_id}, but this node cannot be loaded, see debug level log for details.",
                MIDCOM_LOG_INFO);
            return false;
        }

        $this->_load_leaves($this->_nodes[$node_id]);

        return (array_key_exists($leaf_id, $this->_leaves));
    }

    /**
     * This will give you a key-value pair describeing the leaf with the ID
     * $node_id. The defined keys are described above in leaf data interchange
     * format. You will get false if the leaf ID is invalid.
     *
     * @param string $leaf_id	The leaf-id to be retrieved.
     * @return Array		The leaf-data as outlined in the class introduction, false on failure
     */
    // Keep this doc in sync with midcom_helper_nav
    function get_leaf ($leaf_id)
    {
        if (! $this->_check_leaf_id($leaf_id))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("This leaf is unkown, aborting.", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        return $this->_leaves[$leaf_id];
    }

    /**
     * This is a helper function used by midcom_helper_nav::resolve_guid(). It
     * checks if the object denoted by the passed GUID is already loaded into
     * memory and returns it, if available. This should speed up GUID lookup heavy
     * code.
     *
     * Access is restricted to midcom_helper_nav::resolve_guid().
     *
     * @access protected
     * @param GUID $guid The GUID to look up in the in-memory cache.
     * @return Array A NAP structure if the GUID is known, null otherwise.
     */
    function get_loaded_object_by_guid($guid)
    {
        if (! array_key_exists($guid, $this->_guid_map))
        {
            return null;
        }
        return $this->_guid_map[$guid];
    }

    /**
     * Returns the ID of the node to which $leaf_id is accociated to, false
     * on failure.
     *
     * @param string $leaf_id	The Leaf-ID to search an uplink for.
     * @return int 			The ID of the Node for which we have a match, or false on failure.
     */
    // Keep this doc in sync with midcom_helper_nav
    function get_leaf_uplink($leaf_id)
    {
        debug_push ("_basicnav::get_leaf_uplink");

        if (! $this->_check_leaf_id($leaf_id))
        {
            debug_add("This leaf is unkown, aborting.", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        debug_pop ();
        return $this->_leaves[$leaf_id][MIDCOM_NAV_NODEID];

    }

    /**
     * Returns the ID of the node to which $node_id is accociated to, false
     * on failure. The root node's uplink is -1.
     *
     * @param int $node_id	The Leaf-ID to search an uplink for.
     * @return int 			The ID of the Node for which we have a match, -1 for the root node, or false on failure.
     */
    // Keep this doc in sync with midcom_helper_nav
    function get_node_uplink($node_id)
    {
        debug_push ("_basicnav::get_node_uplink");

        if (! is_numeric($node_id))
        {
            debug_add("Parameter passed is no integer: [$node_id]", MIDCOM_LOG_ERROR);
            debug_print_type('Type was:', $node_id);
            debug_pop();
            return false;
        }

        if (!array_key_exists($node_id, $this->_nodes))
        {
            if ($this->_loadNode($node_id) != MIDCOM_ERROK)
            {
                debug_pop();
                return false;
            }
        }

        debug_pop();
        return $this->_nodes[$node_id][MIDCOM_NAV_NODEID];
    }

    /**
     * @ignore
     */
    function _dump()
    {
        // Debug Helper, dumps the cache status to the debug system.
        debug_print_r("Node Cache Dump:", $this->_nodes, MIDCOM_LOG_DEBUG);
        debug_print_r("Leaf Cache Dump:", $this->_leaves, MIDCOM_LOG_DEBUG);
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
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_print_function_stack("XXXX DEPRECATED XXXX called from here:");
        debug_pop();
        return null;

        static $cache = Array();

        if (! is_array($nap_object))
        {
            debug_add('Invalid object for get_toolbar_definition: Not an array.', MIDCOM_LOG_INFO);
            debug_print_r('Passed argument was:', $nap_object);
            debug_pop();
            return null;
        }

        debug_print_r('Looking up toolbar for this NAP object:', $nap_object);

        $result = null;

        if (array_key_exists($nap_object[MIDCOM_NAV_ID], $cache))
        {
            $result = $cache[$nap_object[MIDCOM_NAV_ID]][MIDCOM_NAV_TOOLBAR];
        }
        else if ($nap_object[MIDCOM_NAV_TYPE] == 'node')
        {
            $node = $this->_get_node_from_database($nap_object[MIDCOM_NAV_ID]);
            $cache[$nap_object[MIDCOM_NAV_ID]] = $node;
            $result = $cache[$nap_object[MIDCOM_NAV_ID]][MIDCOM_NAV_TOOLBAR];
        }
        else if ($nap_object[MIDCOM_NAV_TYPE] == 'leaf')
        {
            $node = $this->_get_node_from_database($nap_object[MIDCOM_NAV_NODEID]);
            $leaves = $this->_get_leaves_from_database($node);
            $cache = array_merge($cache, $leaves);
            $result = $cache[$nap_object[MIDCOM_NAV_ID]][MIDCOM_NAV_TOOLBAR];
        }
        else
        {
            debug_add('Invalid object for get_toolbar_definition: Invalid MIDCOM_NAV_TYPE.', MIDCOM_LOG_INFO);
            debug_print_r('Passed argument was:', $nap_object);
        }

        debug_pop();
        return $result;
    }

}


?>
