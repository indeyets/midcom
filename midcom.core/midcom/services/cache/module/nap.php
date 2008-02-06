<?php
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:nap.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the NAP/Metadata caching module. It provides the basic management functionality
 * for the various backend databases which will be created based on the root topic of the
 * NAP trees (thus the cache can be shared between AIS and on-site pages).
 *
 * The actual handling of the various db's is done with _basicnav.php and metadata.php, this
 * class is responsible for the creation of backend instances and invalidation for both NAP
 * and Metadata cache objects. (Which implies that it is fully aware of the data structures
 * stored in the cache.)
 *
 * All entries are indexed by their Midgard Object GUID. The entries in the NAP cache
 * basically resemble the arrays within the basicnav node/leaf cache, while the metadata
 * cache is a copy of the actual metadata property cache of the midcom_helper_metadata object.
 *
 * NAP/Metadata caches can be shared over multiple sites, as all site specific data (like
 * site prefixes) are evaluated during runtime.
 *
 * Most of the cache update work is done in midcom_helper__basicnav,
 * so you should look there for details about the caching strategy.
 *
 * <b>Implementation notes:</b>
 *
 * Currently, the metadata object is not cached. Instead it relies on the NAP object copies
 * to work in a cached fashion: It uses the members of the object copy from the cache for
 * all basic operations (using the $object->$domain_$name feature of Midgard in combination
 * with variable variables).
 *
 * @see midcom_helper__basicnav
 * @see midcom_helper_metadata
 *
 * @package midcom.services
 */
class midcom_services_cache_module_nap extends midcom_services_cache_module
{
    /**#@+
     * Internal runtime state variable.
     *
     * @access private
     */

    /**
     * The configuration to use to start up the backend drivers. Initialized during
     * startup from the MidCOM configuration key cache_module_nap_backend.
     *
     * @var Array
     */
    var $_backend_config = null;

    /**
     * This array maps GUIDs to backend instances. This is required as AIS sites use
     * the backend instance related to the live site. See _check_for_topic() for details.
     *
     * @var Array
     */
    var $_guid_backend_map = Array();

    /**#@-*/

    /**
     * Module constructor, relay to base class.
     */
    function midcom_services_cache_module_nap()
    {
        parent::midcom_services_cache_module();
    }

    /**
     * Initialization event handler.
     *
     * It will load the cache backends for the current MidCOM topic.
     *
     * Initializes the backend configuration.
     */
    function _on_initialize()
    {
        $this->_backend_config = $GLOBALS['midcom_config']['cache_module_nap_backend'];
        if (! array_key_exists('directory', $this->_backend_config))
        {
            $this->_backend_config['directory'] = 'nap/';
        }
        if (! array_key_exists('driver', $this->_backend_config))
        {
            $this->_backend_config['driver'] = 'dba';
        }
        $this->_backend_config['auto_serialize'] = true;

        $this->_check_for_topic($GLOBALS['midcom_config']['midcom_root_topic_guid']);
    }

    /**
     * Returns the NAP cache associated with the topic tree identified by the
     * parameter $root_topic_guid. The constructed cache database name will be
     * "NAP_{$root_topic_guid}".
     *
     * It verifies, that the cache databases related to the root topic
     *
     * @param GUID $root_topic_guid The GUID of the root topic which is looked for.
     */
    function & get_nap_cache ($root_topic_guid)
    {
        $this->_check_for_topic($root_topic_guid);
        return $this->_guid_backend_map[$root_topic_guid];
    }

    /**
     * Internal helper, which ensures that the cache databases for root topic guid
     * passed to the function are loaded.
     *
     * For compatibility with separated AIS/on-site Page MidCOM installations, the method
     * will load the topic from the database and map all requests to a topic which is
     * of a midcom.admin.content type to the corresponding root content topic. Note, that
     * this is actually a bug in the core's context separation, which I have not yet
     * found. Normally, all instances of basicnav should work within an on-site-context
     * and therefore this "fallback" should not be necessary. Unfortunately, sometimes
     * the context information seems to get mixed up, which results in AIS writing NAP
     * information to the wrong cache file.
     *
     * @param GUID $root_topic_guid The GUID of the root topic which is looked for.
     * @access private
     */
    function _check_for_topic ($guid)
    {
        if (! array_key_exists($guid, $this->_guid_backend_map))
        {
            $topic = new midcom_db_article($guid);
            // Don't use generate_error, as there is no MidCOM instance there yet.
            if (! $topic)
            {
                debug_print_r("Retrieved topic was: ", $topic);
                die("Tried to load the topic {$guid} for NAP cache backend creation, which failed: " . mgd_errstr());
            }

            // Check, whether we are talking to AIS
            if ($topic->component == 'midcom.admin.content')
            {
                $member = 'midcom.admin.content_root_topic';
                $real_guid = $topic->$member;
                if (array_key_exists($real_guid, $this->_guid_backend_map))
                {
                    $backend =& $this->_guid_backend_map[$real_guid];
                }
                else
                {
                    $backend =& $this->_create_backend("NAP_{$real_guid}", $this->_backend_config);
                    $this->_guid_backend_map[$real_guid] =& $backend;
                }
            }
            else
            {
                $backend =& $this->_create_backend("NAP_{$guid}", $this->_backend_config);
            }

            $this->_guid_backend_map[$guid] =& $backend;
        }
    }

    /**
     * Invalidates all cache objects related to the GUID specified. This function is aware for
     * NAP / Metadata caches. It will invalidate the node/leaf record pair upon each invalidation.
     *
     * This function only works within the current context, because it looks on the invalidated
     * GUID to handle the invalidation correctly.
     *
     * <b>Note, for GUIDs which cannot be resolved by NAP:</b>
     *
     * It should be safe to just skip this case, because if the object to be invalidated
     * cannot be found, it is not cached anyway (deleted items could be resolved using
     * the resolve_guid code which uses the cache, so they would still be found).
     * Special cases, where objects not available through NAP are updated have to be hanlded
     * by the component anyway.
     *
     * This way, leaf deletions should be safe in all cases (if they are cached, they can
     * still be resolved, if not, they aren't in the cache anyway). The Datamanager tries
     * to catch leaf creations using its internal creation mode flag, invalidating the
     * current content topic instead of the actual object in this case. Note, that this happens
     * directly after object creation, not during the regular safe cycle.
     *
     * See the automatic index invalidation code of the Datamanager for additional details.
     *
     * @todo Find a way to propagate leaf additions/deletions to to topic which must be invalidated in all
     * places necessary, or MIDCOM_NAV_LEAVES will be broken.
     *
     * @param string $guid The GUID to invalidate.
     */
    function invalidate($guid)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $nav = new midcom_helper_nav();
        $napobject = $nav->resolve_guid($guid);

        if ($napobject === false)
        {
            // Ignoring this should be safe, see the method documentation for details.

            debug_add("We failed to resolve the GUID {$guid} with NAP, apparently it is not cached or no valid NAP node, skipping it therefore.",
                MIDCOM_LOG_INFO);
            debug_pop();
            return;
        }

        $rootnode = $nav->get_node($nav->get_root_node());
        $nap_cache =& $this->get_nap_cache($rootnode[MIDCOM_NAV_GUID]);

        if ($napobject[MIDCOM_NAV_TYPE] == 'leaf')
        {
            $node_id = $napobject[MIDCOM_NAV_NODEID];
        }
        else
        {
            $node_id = $napobject[MIDCOM_NAV_ID];
        }

        $nap_cache->open(true);
        if ($nap_cache->exists($node_id))
        {
            $nap_cache->remove($node_id);
        }
        else
        {
            debug_add("The node was not found in the cache, ignoring it though, as there is obviosuly nothing to invalidate then.",
                MIDCOM_LOG_WARN);
        }

        $leaves_key = "{$node_id}-leaves";
        if ($nap_cache->exists($leaves_key))
        {
            $nap_cache->remove($leaves_key);
        }
        else
        {
            debug_add("The node was not found in the cache, ignoring it though, as there is obviosuly nothing to invalidate then.",
                MIDCOM_LOG_WARN);
        }

        debug_pop();
    }




}
?>