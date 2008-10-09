<?php
/**
 * @package net.nemein.alphabeticalindex
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which URLs should be handled by this module.
 *
 * @package net.nemein.alphabeticalindex
 */
class net_nemein_alphabeticalindex_viewer extends midcom_baseclasses_components_request
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
        /**
         * Prepare the request switch, which contains URL handlers for the component
         */

        $this->_request_data['topic'] =& $this->_topic;

        // Handle /
        $this->_request_switch['index'] = array
        (
            'handler' => Array('net_nemein_alphabeticalindex_handler_index', 'index'),
        );

        // Handle /create/[type]
        $this->_request_switch['create'] = array
        (
            'handler' => Array('net_nemein_alphabeticalindex_handler_create', 'create'),
            'fixed_args' => Array('create'),
            'variable_args' => 1
        );

        // Handle /edit/[guid]
        $this->_request_switch['edit'] = array
        (
            'handler' => Array('net_nemein_alphabeticalindex_handler_edit', 'edit'),
            'fixed_args' => Array('edit'),
            'variable_args' => 1
        );

        // Handle /delete/[guid]
        $this->_request_switch['delete'] = array
        (
            'handler' => Array('net_nemein_alphabeticalindex_handler_admin', 'delete'),
            'fixed_args' => Array('delete'),
            'variable_args' => 1
        );

        // Handle /clear_index/
        $this->_request_switch['clear_index'] = array
        (
            'handler' => Array('net_nemein_alphabeticalindex_handler_admin', 'clearindex'),
            'fixed_args' => Array('clear_index')
        );

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/net.nemein.alphabeticalindex/styles/main.css",
            )
        );

        $_MIDCOM->enable_jquery();
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/net.nemein.alphabeticalindex/js/main.js');
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
     * Populates the node toolbar depending on the user's rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {
        /*
        if ($this->_content_topic->can_do('midgard:create'))
        {
            foreach (array_keys($this->_request_data['schemadb']) as $name)
            {
                $this->_node_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "create/{$name}/",
                    MIDCOM_TOOLBAR_LABEL => sprintf
                    (
                        $this->_l10n_midcom->get('create %s'),
                        $this->_request_data['schemadb'][$name]->description
                    ),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                ));
            }
        }
        */
        if ($this->_topic->can_do('midgard:create'))
        {
            $this->_node_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "create/external/",
                MIDCOM_TOOLBAR_LABEL => sprintf
                (
                    $this->_l10n_midcom->get('create %s'),
                    $this->_l10n->get('external item')
                ),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'n',
            ));
            $this->_node_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "create/internal/",
                MIDCOM_TOOLBAR_LABEL => sprintf
                (
                    $this->_l10n_midcom->get('create %s'),
                    $this->_l10n->get('internal item')
                ),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'n',
            ));
        }

        if ($this->_topic->can_do('midgard:delete'))
        {
            $this->_node_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "clear_index/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('Clear index'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 't',
            ));
        }
    }

    /**
     * The handle callback populates the toolbars.
     */
    function _on_handle($handler, $args)
    {
        $this->_request_data['schemadb'] =
            midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));

        $this->_populate_node_toolbar();

        return true;
    }

}

?>