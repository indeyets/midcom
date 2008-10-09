<?php
/**
 * @package net.nemein.netmon
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which URLs should be handled by this module.
 *
 * @package net.nemein.netmon
 */
class net_nemein_netmon_viewer extends midcom_baseclasses_components_request
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

        // Handle /host/<guid>.html
        $this->_request_switch['view-host'] = array
        (
            'fixed_args' => array('host'),
            'variable_args' => 1,
            'handler' => Array('net_nemein_netmon_handler_host_view', 'view'),
        );
        // Handle /host/edit/<guid>.html
        $this->_request_switch['edit-host'] = array
        (
            'fixed_args' => array('host', 'edit'),
            'variable_args' => 1,
            'handler' => Array('net_nemein_netmon_handler_host_admin', 'edit'),
        );
        // Handle /host/delete/<guid>.html
        $this->_request_switch['delete-host'] = array
        (
            'fixed_args' => array('host', 'delete'),
            'variable_args' => 1,
            'handler' => Array('net_nemein_netmon_handler_host_admin', 'delete'),
        );
        // Handle /create/host.html
        $this->_request_switch['create-host'] = array
        (
            'fixed_args' => array('create', 'host'),
            'handler' => Array('net_nemein_netmon_handler_host_create', 'create'),
        );
        // Handle /create/host/<guid>.html
        $this->_request_switch['create-host-wparent'] = array
        (
            'fixed_args' => array('create', 'host'),
            'variable_args' => 1,
            'handler' => Array('net_nemein_netmon_handler_host_create', 'create'),
        );

        // Handle /hostgroup/<guid>.html
        $this->_request_switch['view-hostgroup'] = array
        (
            'fixed_args' => array('hostgroup'),
            'variable_args' => 1,
            'handler' => Array('net_nemein_netmon_handler_hostgroup_view', 'view'),
        );
        // Handle /hostgroup/edit/<guid>.html
        $this->_request_switch['edit-hostgroup'] = array
        (
            'fixed_args' => array('hostgroup', 'edit'),
            'variable_args' => 1,
            'handler' => Array('net_nemein_netmon_handler_hostgroup_admin', 'edit'),
        );
        // Handle /hostgroup/delete/<guid>.html
        $this->_request_switch['delete-hostgroup'] = array
        (
            'fixed_args' => array('hostgroup', 'delete'),
            'variable_args' => 1,
            'handler' => Array('net_nemein_netmon_handler_hostgroup_admin', 'delete'),
        );
        // Handle /create/hostgroup.html
        $this->_request_switch['create-hostgroup'] = array
        (
            'fixed_args' => array('create', 'hostgroup'),
            'handler' => Array('net_nemein_netmon_handler_hostgroup_create', 'create'),
        );
        // Handle /create/hostgroup/<guid>.html
        $this->_request_switch['create-hostgroup-wparent'] = array
        (
            'fixed_args' => array('create', 'hostgroup'),
            'variable_args' => 1,
            'handler' => Array('net_nemein_netmon_handler_hostgroup_create', 'create'),
        );


        // Handle /
        $this->_request_switch['index'] = array
        (
            'handler' => Array('net_nemein_netmon_handler_index', 'index'),
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

        $document = $indexer->new_document($dm);
        $document->topic_guid = $topic->guid;
        $document->topic_url = $node[MIDCOM_NAV_FULLURL];
        $document->read_metadata_from_object($dm->storage->object);
        $document->component = $topic->component;
        $indexer->index($document);
    }

    /**
     * Populates the node toolbar depending on the user's rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {
        // TODO: use can_user_do
        if ($this->_topic->can_do('midgard:create'))
        {
            foreach (array_keys($this->_request_data['schemadb']) as $name)
            {
                $this->_node_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "create/{$name}.html",
                    MIDCOM_TOOLBAR_LABEL => sprintf
                    (
                        $this->_l10n_midcom->get('create %s'),
                        $this->_request_data['schemadb'][$name]->description
                    ),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                ));
            }
        }
    }

    /**
     * The handle callback populates the toolbars.
     */
    function _on_handle($handler, $args)
    {
        $this->_request_data['schemadb'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));

        $this->_populate_node_toolbar();

        return true;
    }

}

?>