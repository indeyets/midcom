<?php
/**
 * @package org.routamc.photostream
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which url's should be handled by this module.
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
        // *** Prepare the request switch ***

        // Handle /config
        $this->_request_switch['config'] = array
        (
            'handler' => array
            (
                'midcom_core_handler_configdm',
                'configdm'
            ),
            'schemadb' => 'file:/org/routamc/photostream/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => array
            (
                'config'
            ),
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
        
        /* not implemented yet
        $this->_request_switch['api-metaweblog'] = Array
        (
            'handler' => Array('org_routamc_photostream_handler_api_metaweblog', 'server'),
            'fixed_args' => Array('api', 'metaweblog'),
        );
        */

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

    /**
     * Indexes an article.
     *
     * This function is usually called statically from various handlers.
     *
     * @param midcom_helper_datamanager2_datamanager $dm The Datamanager encaspulating the event.
     * @param midcom_services_indexer $indexer The indexer instance to use.
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
        $indexer->index($document);
    }

    /**
     * Populates the node toolbar depending on the users rights.
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

        return true;
    }

}

?>
