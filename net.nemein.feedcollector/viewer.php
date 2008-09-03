<?php
/**
 * @package net.nemein.feedcollector
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which URLs should be handled by this module.
 *
 * @package net.nemein.feedcollector
 */
class net_nemein_feedcollector_viewer extends midcom_baseclasses_components_request
{
    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);
    }

    /**
     * Initialize the request switch and the content topic.
     *
     * @access protected
     */
    function _on_initialize()
    {
        $this->_determine_content_topic();
        $this->_request_data['content_topic'] =& $this->_content_topic;

        /**
         * Prepare the request switch, which contains URL handlers for the component
         */

        // Handle /config
        $this->_request_switch['config'] = array
        (
            'handler' => Array('midcom_helper_dm2config_config', 'config'),
            'fixed_args' => Array('config'),
        );

        // Handle /
        $this->_request_switch['index'] = array
        (
            'handler' => Array('net_nemein_feedcollector_handler_index', 'index'),
        );
        // Handle /latest/
        $this->_request_switch['latest'] = array
        (
            'handler' => Array('net_nemein_feedcollector_handler_latest', 'latest'),
            'fixed_args' => Array('latest'),
        );
        // Handle /latest/<NUMBER>
        $this->_request_switch['latest_count'] = array
        (
            'handler' => Array('net_nemein_feedcollector_handler_latest', 'latest'),
            'fixed_args' => Array('latest'),
            'variable_args' => 1,
        );
        // Handle /manage/
        $this->_request_switch['maangement'] = array
        (
            'handler' => Array('net_nemein_feedcollector_handler_manage', 'manage'),
            'fixed_args' => Array('manage'),
        );
        // Handle /manage/delete/<GUID>
        $this->_request_switch['management_delete'] = array
        (
            'handler' => Array('net_nemein_feedcollector_handler_manage', 'delete'),
            'fixed_args' => Array('manage', 'delete'),
            'variable_args' => 1,
        );
        // Handle /manage/edit/<GUID>
        $this->_request_switch['management_edit'] = array
        (
            'handler' => Array('net_nemein_feedcollector_handler_manage', 'edit'),
            'fixed_args' => Array('manage', 'edit'),
            'variable_args' => 1,
        );
        // Handle /create/
        $this->_request_switch['management_create'] = array
        (
            'handler' => Array('net_nemein_feedcollector_handler_create', 'create'),
            'fixed_args' => Array('create'),
        );
    }

    /**
     * Indexes an article.
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
        $author = $_MIDCOM->auth->get_user($dm->storage->object->creator);

        $document = $indexer->new_document($dm);
        $document->topic_guid = $topic->guid;
        $document->component = $topic->component;
        $document->topic_url = $node[MIDCOM_NAV_FULLURL];
        $document->read_metadata_from_object($dm->storage->object);
        $indexer->index($document);
    }


    /**
     * Set the content topic to use. This will check against the configuration setting 'symlink_topic'.
     *
     * @access protected
     */
    function _determine_content_topic()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $guid = $this->_config->get('symlink_topic');
        if (is_null($guid))
        {
            // No symlink topic
            // Workaround, we should talk to a DBA object automatically here in fact.
            $this->_content_topic = new midcom_db_topic($this->_topic->id);
            debug_pop();
            return;
        }

        $this->_content_topic = new midcom_db_topic($guid);

        // Validate topic.

        if (! $this->_content_topic)
        {
            debug_add('Failed to open symlink content topic, (might also be an invalid object) last Midgard Error: '
                . mgd_errstr(), MIDCOM_LOG_ERROR);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to open symlink content topic.');
            // This will exit.
        }

        if ($this->_content_topic->component != 'net.nehmer.static')
        {
            debug_print_r('Retrieved topic was:', $this->_content_topic);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Symlink content topic is invalid, see the debug level log for details.');
            // This will exit.
        }

        debug_pop();
    }

    /**
     * Populates the node toolbar depending on the user's rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {
        if (   $this->_topic->can_do('midgard:create'))
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'create/',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create collection'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n->get('create helptext'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                )
            );
        }
        if (   $this->_topic->can_do('midgard:update'))
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'manage/',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('management'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n->get('management helptext'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                )
            );
        }
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

        if ($this->_config->get('constrain_collection_to_site'))
        {
            // Add an INTREE constraint to the topic chooser
            $root_topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ROOTTOPIC);
            foreach ($this->_request_data['schemadb'] as $schema_name => $schema)
            {
                if (!isset($schema->fields['feedtopic']))
                {
                    continue;
                }
                
                $this->_request_data['schemadb'][$schema_name]->fields['feedtopic']['widget_config']['constraints'][] = array
                (
                    'field' => 'up',
                    'op' => 'INTREE',
                    'value' => $root_topic->id,
                );
            }
        }
        
        $this->_populate_node_toolbar();

        return true;
    }

}

?>