<?php
/**
 * @package org.routamc.photostream
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which URLs should be handled by this module.
 *
 * @package org.routamc.photostream
 */
class org_routamc_photostream_viewer extends midcom_baseclasses_components_request
{
    function org_routamc_photostream_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
    }

    /**
     * Initialize the request switch and the content topic.
     *
     * @access protected
     */
    function _on_initialize()
    {
        // Prepare the symlink topic
        if ($this->_config->get('symlink_topic'))
        {
            $topic = new midcom_db_topic($this->_config->get('symlink_topic'));
            
            // Symlink topic not found
            if (   !$topic
                || !$topic->guid)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Symlinked topic not found');
            }
            
            $this->_request_data['content_topic'] =& $topic;
        }
        else
        {
            $this->_request_data['content_topic'] =& $this->_topic;
        }

        // *** Prepare the request switch ***

        // Handle /config
        $this->_request_switch['config'] = array
        (
            'handler' => array ('midcom_helper_dm2config_config', 'config'),
            'fixed_args' => array ('config'),
        );

        // Handle /upload
        $this->_request_switch['upload'] = array
        (
            'handler' => array
            (
                'org_routamc_photostream_handler_upload',
                'upload'
            ),
            'fixed_args' => array
            (
                'upload'
            ),
        );

        // Handle /latest/all/<n>
        $this->_request_switch['photostream_latest_all'] = array
        (
            'handler' => array
            (
                'org_routamc_photostream_handler_list',
                'photostream_latest'
            ),
            'fixed_args' => array
            (
                'latest',
                'all',
            ),
            'variable_args' => 1,
        );

        // Handle /latest/<username>/<n>
        $this->_request_switch['photostream_latest'] = array
        (
            'handler' => array
            (
                'org_routamc_photostream_handler_list',
                'photostream_latest'
            ),
            'fixed_args' => array
            (
                'latest',
            ),
            'variable_args' => 2,
        );

        // Handle /between/all/<from>/<to>
        $this->_request_switch['photostream_between_all'] = array
        (
            'handler' => array
            (
                'org_routamc_photostream_handler_list',
                'photostream_between'
            ),
            'fixed_args' => array
            (
                'between',
                'all',
            ),
            'variable_args' => 2,
        );

        // Handle /between/<username>/<from>/<to>
        $this->_request_switch['photostream_between'] = array
        (
            'handler' => array
            (
                'org_routamc_photostream_handler_list',
                'photostream_between'
            ),
            'fixed_args' => array
            (
                'between',
            ),
            'variable_args' => 3,
        );

        // Handle /tag/all/<tag>
        $this->_request_switch['photostream_tag_all'] = array
        (
            'handler' => array
            (
                'org_routamc_photostream_handler_list',
                'photostream_tag'
            ),
            'fixed_args' => array
            (
                'tag',
                'all',
            ),
            'variable_args' => 1,
        );

        // Handle /tag/<username>/<tag>
        $this->_request_switch['photostream_tag'] = array
        (
            'handler' => array
            (
                'org_routamc_photostream_handler_list',
                'photostream_tag'
            ),
            'fixed_args' => array
            (
                'tag',
            ),
            'variable_args' => 2,
        );

        // Handle /tag/all/
        $this->_request_switch['photostream_tags_all'] = array
        (
            'handler' => array
            (
                'org_routamc_photostream_handler_list',
                'photostream_tags'
            ),
            'fixed_args' => array
            (
                'tag',
                'all',
            ),
        );

        // Handle /tag/<username>/
        $this->_request_switch['photostream_tags'] = array
        (
            'handler' => array
            (
                'org_routamc_photostream_handler_list',
                'photostream_tags'
            ),
            'fixed_args' => array
            (
                'tag',
            ),
            'variable_args' => 1,
        );

        // Handle /rated/all/<tag>
        $this->_request_switch['photostream_rated_all'] = array
        (
            'handler' => array
            (
                'org_routamc_photostream_handler_list',
                'photostream_rated'
            ),
            'fixed_args' => array
            (
                'rated',
                'all',
            ),
        );

        // Handle /rated/<username>/<rating>
        $this->_request_switch['photostream_rated'] = array
        (
            'handler' => array
            (
                'org_routamc_photostream_handler_list',
                'photostream_rated'
            ),
            'fixed_args' => array
            (
                'rated',
            ),
            'variable_args' => 2,
        );

        // Handle /list/all
        $this->_request_switch['photostream_list_all'] = array
        (
            'handler' => array
            (
                'org_routamc_photostream_handler_list',
                'photostream_list'
            ),
            'fixed_args' => array
            (
                'list',
                'all',
            ),
        );

        // Handle /list/<username>
        $this->_request_switch['photostream_list'] = array
        (
            'handler' => array
            (
                'org_routamc_photostream_handler_list',
                'photostream_list'
            ),
            'fixed_args' => array
            (
                'list',
            ),
            'variable_args' => 1,
        );
        
        // List configured amount of random photos
        // Handle /list/random/
        $this->_request_switch['photostream_list_random'] = array
        (
            'handler' => array ('org_routamc_photostream_handler_list', 'random'),
            'fixed_args' => array('random'),
        );
        
        // Handle /list/random/<username|count>/
        $this->_request_switch['photostream_list_random_count'] = array
        (
            'handler' => array ('org_routamc_photostream_handler_list', 'random'),
            'fixed_args' => array('random'),
            'variable_args' => 1,
        );
        
        // Handle /list/random/<username>/<count>/
        $this->_request_switch['photostream_list_random_user'] = array
        (
            'handler' => array ('org_routamc_photostream_handler_list', 'random'),
            'fixed_args' => array('random'),
            'variable_args' => 2,
        );

        // Handle /batch/<batch_id>
        $this->_request_switch['photostream_batch'] = array
        (
            'handler' => array
            (
                'org_routamc_photostream_handler_list',
                'photostream_batch'
            ),
            'fixed_args' => array
            (
                'batch',
            ),
            'variable_args' => 1,
        );

        // Handle /photo/<guid>
        $this->_request_switch['photo'] = Array
        (
            'handler' => Array('org_routamc_photostream_handler_view', 'view'),
            'fixed_args' => Array('photo'),
            'variable_args' => 1,
        );

        // Handler /photo/<guid>/<limiter>/<limit>
        $this->_request_switch['photo_args_3'] = Array
        (
            'handler' => Array('org_routamc_photostream_handler_view', 'view'),
            'fixed_args' => array('photo'),
            'variable_args' => 3,
        );

        // Handler /photo/<guid>/<limiter>/<limit>/<limit>
        $this->_request_switch['photo_args_4'] = Array
        (
            'handler' => Array('org_routamc_photostream_handler_view', 'view'),
            'fixed_args' => array('photo'),
            'variable_args' => 4,
        );

        // Handler /photo/<guid>/<limiter>/<limit>/<limit>/<limit>
        $this->_request_switch['photo_args_5'] = Array
        (
            'handler' => Array('org_routamc_photostream_handler_view', 'view'),
            'fixed_args' => array('photo'),
            'variable_args' => 5,
        );

        // Handle /photo/raw/<guid>
        $this->_request_switch['photo_raw'] = Array
        (
            'handler' => Array('org_routamc_photostream_handler_view', 'view'),
            'fixed_args' => Array('photo', 'raw'),
            'variable_args' => 1,
        );

        // Handle /photo/<guid>/<gallery>
        $this->_request_switch['photo_gallery'] = Array
        (
            'handler' => Array('org_routamc_photostream_handler_view', 'view'),
            'fixed_args' => Array('photo'),
            'variable_args' => 2,
        );

        // Handle /edit/<guid>
        $this->_request_switch['edit'] = Array
        (
            'handler' => Array('org_routamc_photostream_handler_admin', 'edit'),
            'fixed_args' => Array('edit'),
            'variable_args' => 1,
        );

        // Handle /delete/guid
        $this->_request_switch['delete'] = Array
        (
            'handler' => Array('org_routamc_photostream_handler_admin', 'delete'),
            'fixed_args' => Array('delete'),
            'variable_args' => 1,
        );

        // Handle /recreate
        $this->_request_switch['recreate'] = Array
        (
            'handler' => Array('org_routamc_photostream_handler_admin', 'recreate'),
            'fixed_args' => Array('recreate'),
            'variable_args' => 0,
        );

        $this->_request_switch['api-email'] = Array
        (
            'handler' => Array('org_routamc_photostream_handler_api_email', 'import'),
            'fixed_args' => Array('api', 'email'),
        );
        
        // Handle /sort/<property>/
        $this->_request_switch['sort_by'] = Array
        (
            'handler' => Array('org_routamc_photostream_handler_list', 'sort'),
            'fixed_args' => array('sort'),
            'variable_args' => 1,
        );

        // Handle /sort/<property>/<direction>/
        $this->_request_switch['sort_by_direction'] = Array
        (
            'handler' => Array('org_routamc_photostream_handler_list', 'sort'),
            'fixed_args' => array('sort'),
            'variable_args' => 2,
        );

        /* not implemented yet
        $this->_request_switch['api-metaweblog'] = Array
        (
            'handler' => Array('org_routamc_photostream_handler_api_metaweblog', 'server'),
            'fixed_args' => Array('api', 'metaweblog'),
        );
        */
        
        // Approving methods
        if ($this->_config->get('moderate_uploaded_photos'))
        {
            // List the photos that haven't been accepted
            $this->_request_switch['moderate_list'] = array
            (
                'handler' => Array('org_routamc_photostream_handler_admin', 'moderate'),
                'fixed_args' => Array('moderate'),
            );
            // List the rejected photos
            $this->_request_switch['moderate_rejected'] = array
            (
                'handler' => Array('org_routamc_photostream_handler_admin', 'moderate'),
                'fixed_args' => Array('moderate', 'rejected'),
            );
            // List the rejected photos
            $this->_request_switch['moderate_all'] = array
            (
                'handler' => Array('org_routamc_photostream_handler_admin', 'moderate'),
                'fixed_args' => Array('moderate', 'all'),
            );
            // Show an item for moderating
            $this->_request_switch['moderate_item'] = array
            (
                'handler' => Array('org_routamc_photostream_handler_admin', 'view'),
                'fixed_args' => Array('moderate'),
                'variable_args' => 1,
            );
        }

        if ($this->_config->get('entry_page') === 'straight')
        {
            // Handle /
            $this->_request_switch['photostream_list_all_frontpage'] = array
            (
                'handler' => array
                (
                    'org_routamc_photostream_handler_list',
                    'photostream_list'
                ),
                'fixed_args' => Array(),
            );
        }
        else
        {
            // Handle /
            $this->_request_switch['index'] = array
            (
                'handler' => array
                (
                    'org_routamc_photostream_handler_index',
                    'index'
                ),
            );
        }

        $this->_register_feed_handlers();
    }

    function _register_feed_handlers()
    {
        foreach ($this->_request_switch as $handler_id => $switch_data)
        {
            if ($switch_data['handler'][0] !== 'org_routamc_photostream_handler_list')
            {
                // We only care about the list views
                continue;
            }
            $new_id = "feed:{$handler_id}";
            if (isset($this->_request_switch[$new_id]))
            {
                continue;
            }
            $new_switch = $switch_data;

            // switch handler to the feed dispatcher
            $new_switch['handler'] = array('org_routamc_photostream_handler_feed', 'dispatcher');
            // add a variable arg to end of list for feed type
            if (!isset($new_switch['variable_args']))
            {
                $new_switch['variable_args'] = 0;
            }
            $new_switch['variable_args']++;

            if (!$this->_sanity_check_switch($new_switch))
            {
                // ULR-space clash, prepend /feed/ to list fixed args
                array_unshift($new_switch['fixed_args'], 'feed');
            }
            if (!$this->_sanity_check_switch($new_switch))
            {
                // URL-space still clashes
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("New switch '{$new_id}' would never be matched, not adding", MIDCOM_LOG_WARN);
                debug_pop();
                continue;
            }

            // Add the new switch
            $this->_request_switch[$new_id] = $new_switch;

            // If we were not forced to use the feed url space earlier add it anyway so we have at least one consistent interface
            if (   isset($new_switch['fixed_args'][0])
                && $new_switch['fixed_args'][0] !== 'feed')
            {
                array_unshift($new_switch['fixed_args'], 'feed');
                $new_id = "feed2:{$handler_id}";
                $this->_request_switch[$new_id] = $new_switch;
            }

            unset($new_id, $new_switch);
        }
    }

    function _sanity_check_switch(&$new_switch)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        foreach ($this->_request_switch as $handler_id => $switch_data)
        {
            debug_add("Comparing with handler '{$handler_id}'");
            switch(true)
            {
                case (   !isset($switch_data['fixed_args'])
                      && isset($new_switch['fixed_args'])):
                    debug_add('existing switch has not-set fixed_args and new_switch has');
                    $fixed_diff = true;
                    break;
                case (   !isset($new_switch['fixed_args'])
                      && isset($switch_data['fixed_args'])):
                    debug_add('new_switch has not-set fixed_args and existing switch has');
                    $fixed_diff = true;
                    break;
                case (   !is_array($switch_data['fixed_args'])
                      && is_array($new_switch['fixed_args'])):
                    debug_add('existing switch fixed_args is not array, new_switch fixed_args is');
                    $fixed_diff = true;
                    break;
                case (   !is_array($new_switch['fixed_args'])
                      && is_array($switch_data['fixed_args'])):
                    debug_add('new_switch fixed_args is not array, existing switch fixed_args is');
                    $fixed_diff = true;
                    break;
                case (   is_array($switch_data['fixed_args'])
                      && is_array($new_switch['fixed_args'])):
                    debug_add('new_switch and existing switch fixed_args are both arrays, calling array_diff');
                    $fixed_diff = array_diff($switch_data['fixed_args'], $new_switch['fixed_args']);
                    if (empty($fixed_diff))
                    {
                        /**
                         * array_diff() returns an array consisting of all elements in $array1 that are not in $array2,
                         * NOT a true diff, thus we need to check again with reversed order
                         */
                        $fixed_diff = array_diff($new_switch['fixed_args'], $switch_data['fixed_args']);
                    }
                    break;
                default:
                    debug_add('defaulting fixed_diff to false');
                    $fixed_diff = false;
            }
            debug_print_r('$fixed_diff: ', $fixed_diff);
            if (!empty($fixed_diff))
            {
                // Fixed args differ
                continue;
            }
            if (   !isset($switch_data['variable_args'])
                || !isset($new_switch['variable_args'])
                || $switch_data['variable_args'] < $new_switch['variable_args'])
            {
                // Variable args do not overlap
                continue;
            }
            debug_add("handler '{$handler_id}' already implements the url space", MIDCOM_LOG_INFO);
            debug_print_r('$new_switch was: ', $new_switch, MIDCOM_LOG_INFO);
            debug_print_r("\$this->_request_switch['{$handler_id}'] was: ", $switch_data, MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }
        debug_pop();
        return true;
    }

    /**
     * Indexes a photo object.
     *
     * This function is usually called statically from various handlers.
     *
     * @param midcom_helper_datamanager2_datamanager &$dm The Datamanager encapsulating the event.
     * @param midcom_services_indexer &$indexer The indexer instance to use.
     * @param midcom_db_topic The topic which we are bound to. If this is not an object, the code
     *     tries to load a new topic instance from the database identified by this parameter.
     */
    function index(&$dm, &$indexer, $topic)
    {
        if (!is_object($topic))
        {
            $tmp = new midcom_db_topic($topic);
            if (! $tmp)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Failed to load the topic referenced by {$topic} for indexing, this is fatal.");
                // This will exit.
            }
            $topic = $tmp;
        }

        // Don't index directly, that would loose a reference due to limitations
        // of the index() method. Needs fixes there.

        $nav = new midcom_helper_nav();
        $node = $nav->get_node($topic->id);

        $document = $indexer->new_document($dm);
        $document->topic_guid = $topic->guid;
        $document->component = $topic->component;
        $document->topic_url = $node[MIDCOM_NAV_FULLURL];
        $document->read_metadata_from_object($dm->storage->object);
        $photo =& $dm->storage->object;
        // Get just the thumbnail img tag
        $thumbnail_tag = preg_replace('%.*(<img.*?/>).*%msi', '\\1', $photo->thumbnail_html);
        // Prepend that to first 200 chars of description
        $desc_base = strip_tags($photo->description);
        if (strlen($desc_base) > 200)
        {
            $abstract = substr($desc_base, 0, 200) . ' ...';
        }
        else
        {
            $abstract = $desc_base;
        }
        $document->abstract = "\n<div class='org_routamc_photostream_indexed_abstract'>\n    <div class='thumbnail_container'>\n        <a href='{$document->document_url}'>{$thumbnail_tag}</a>\n    </div>\n    {$abstract}\n</div>";
        $indexer->index($document);
    }

    /**
     * Populates the node toolbar depending on the user's rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {

        $this->_node_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => 'upload.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('upload photos'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/images.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_topic->can_do('midgard:create'),
                MIDCOM_TOOLBAR_ACCESSKEY => 'n',
            )
        );
        $this->_node_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => 'recreate.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('recreate derived images'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                // TODO: better icon
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/recurring.png',
                // TODO: Better privilege ?
                MIDCOM_TOOLBAR_ENABLED => $this->_topic->can_do('midgard:create'),
            )
        );

        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'config.html',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                )
            );
        }

    }

    /**
     * The handle callback populates the toolbars.
     */
    function _on_handle($handler, $args)
    {
        $this->_request_data['schemadb'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));

        $this->_populate_node_toolbar();

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/org.routamc.photostream/photos.css",
            )
        );

        // the feed dispatcher needs this information, it might be available otherwise but I couldn't find it
        $this->_request_data['request_switch'] =& $this->_request_switch;

        return true;
    }
}
?>