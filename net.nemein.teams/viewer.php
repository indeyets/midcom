<?php
/**
 * @package net.nemein.teams
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which URLs should be handled by this module.
 *
 * @package net.nemein.teams
 */
class net_nemein_teams_viewer extends midcom_baseclasses_components_request
{
    var $_content_topic = null;

    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);

        $this->_content_topic = $topic;
    }

    /**
     * Initialize the request switch and the content topic.
     *
     * @access protected
     */
    function _on_initialize()
    {
        $this->_request_data['content_topic'] =& $this->_content_topic;

        /**
         * Prepare the request switch, which contains URL handlers for the component
         */

        // Handle /config
        $this->_request_switch['config'] = array
        (
            'handler' => Array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/net/nemein/teams/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );

        // Handle /
        $this->_request_switch['index'] = array
        (
            'handler' => Array('net_nemein_teams_handler_team', 'index'),
        );

        // Matches rootgroup
        $this->_request_switch['create-rootgroup'] = array
        (
            'handler' => Array('net_nemein_teams_handler_team', 'rootgroup'),
            'fixed_args' => Array('rootgroup'),
        );

        // // Matches create/profile
        // $this->_request_switch['create-profile'] = array
        // (
        //     'handler' => Array('net_nemein_teams_handler_team', 'create_profile'),
        //     'fixed_args' => Array('create','profile'),
        // );

        // Application /
        $this->_request_switch['application'] = array
        (
            'handler' => Array('net_nemein_teams_handler_team', 'application'),
            'fixed_args' => Array('application'),
            'variable_args' => 1,
        );

        // Shares /
        $this->_request_switch['shares'] = array
        (
            'handler' => Array('net_nemein_teams_handler_team', 'shares'),
            'fixed_args' => Array('shares'),
        );

        // Log /
        $this->_request_switch['log'] = array
        (
            'handler' => Array('net_nemein_teams_handler_admin', 'log'),
            'fixed_args' => Array('log'),
        );

        /*
        // Manage / System
        $this->_request_switch['manage_system'] = array
        (
            'handler' => Array('net_nemein_teams_handler_admin', 'manage_system'),
            'fixed_args' => Array('manage_system'),
        );

        // Manage / lockdown
        $this->_request_switch['manage_lockdown'] = array
        (
            'handler' => Array('net_nemein_teams_handler_admin', 'manage_'),
            'fixed_args' => Array('manage_system'),
        );
         */

        // Manage /
        $this->_request_switch['manage'] = array
        (
            'handler' => Array('net_nemein_teams_handler_admin', 'manage'),
            'fixed_args' => Array('manage'),
        );

        // Manage / Delete
        $this->_request_switch['manage_delete'] = array
        (
            'handler' => Array('net_nemein_teams_handler_admin', 'manage_delete'),
            'fixed_args' => Array('manage', 'delete'),
            'variable_args' => 1,
        );

        // Manage / Team
        $this->_request_switch['manage_team'] = array
        (
            'handler' => Array('net_nemein_teams_handler_admin', 'manage_team'),
            'fixed_args' => Array('manage', 'team'),
            'variable_args' => 1,
        );

        // Handle / Team list
        $this->_request_switch['list'] = array
        (
            'handler' => Array('net_nemein_teams_handler_team', 'list'),
            'fixed_args' => Array('list'),
        );

        // Approve /
        // $this->_request_switch['pending'] = array
        // (
        //     'handler' => Array('net_nemein_teams_handler_team', 'pending'),
        //          'fixed_args' => Array('pending'),
        // );

        // Create /
        $this->_request_switch['create'] = array
        (
            'handler' => Array('net_nemein_teams_handler_team', 'create'),
            'fixed_args' => Array('create'),
        );

        // Quit /
        $this->_request_switch['quit'] = array
        (
            'handler' => Array('net_nemein_teams_handler_team', 'quit'),
            'fixed_args' => Array('quit'),
        );

        // Quit / Confirm
        $this->_request_switch['quit_confirm'] = array
        (
            'handler' => Array('net_nemein_teams_handler_team', 'quit_confirm'),
            'fixed_args' => Array('quit', 'confirm'),
        );

        // Lockdown /
        $this->_request_switch['lockdown'] = array
        (
            'handler' => Array('net_nemein_teams_handler_team', 'lockdown'),
            'fixed_args' => Array('lockdown'),
        );

        // $this->_request_switch['action'] = array
        // (
        //     'handler' => Array('net_nemein_teams_handler_team', 'action'),
        //          'fixed_args' => Array('team'),
        //     'variable_args' => 3,
        // );
        $this->_request_switch['action'] = array
        (
            'handler' => Array('net_nemein_teams_handler_team', 'action'),
            'fixed_args' => Array('team'),
            'variable_args' => 2,
        );
/*
        // Create / home
        $this->_request_switch['create_team_home'] = array
        (
            'handler' => Array('net_nemein_teams_handler_team', 'create_team_home'),
            'fixed_args' => Array('create', 'home'),
            'variable_args' => 1,
        );
*/
    }

    /**
     * Loads the plugin identified by $name. Only the on-site listing is loaded.
     * If the plugin has no on-site interface, no changes are made to the request switch.
     *
     * Each request handler of the plugin is automatically adjusted as follows:
     *
     * - 1st, the registered names of the registered handlers (array keys) are prefixed by
     *   "plugin-{$name}-".
     * - 2nd, all registered handlers are automatically prefixed by the fixed arguments
     *   ("plugin", $name).
     *
     * @param string $name The plugin name as registered in the plugins configuration
     *     option.
     * @access private
     */
    function _load_nna_plugin($name)
    {
        // Validate the plugin name and load the associated configuration
        $plugins = $this->_config->get('plugins');
        if (   ! $plugins
            || ! array_key_exists($name, $plugins))
        {
            return false;
        }
        $plugin_config = $plugins[$name];

        // Load the plugin class, errors are logged by the callee
        if (! $this->_load_nna_plugin_class($name, $plugin_config))
        {
            return false;
        }

        // Load the configuration into the request data, add the configured plugin name as
        // well so that URLs can be built.
        if (array_key_exists('config', $plugin_config))
        {
            $this->_request_data['plugin_config'] = $plugin_config['config'];
        }
        else
        {
            $this->_request_data['plugin_config'] = null;
        }
        $this->_request_data['plugin_name'] = $name;

        // Load remaining configuration, and prepare the plugin, errors are logged by the callee.
        $handlers = call_user_func(array($plugin_config['class'], 'get_plugin_handlers'));
        if (! $this->_prepare_nna_plugin($name, $plugin_config, $handlers))
        {
            return false;
        }
        return true;
    }

    /**
     * Prepares the actual plugin by adding all necessary information to the request
     * switch.
     *
     * @param string $name The plugin name as registered in the plugins configuration
     *     option.
     * @param Array $plugin_config The configuration associated with the plugin.
     * @param Array $handlers The plugin specific handlers without the appropriate prefixes.
     * @access private
     * @return boolean Indicating Success
     */
    function _prepare_nna_plugin ($name, $plugin_config, $handlers)
    {
        foreach ($handlers as $identifier => $handler_config)
        {
            // First, update the fixed args list (be tolerant here)
            if (! array_key_exists('fixed_args', $handler_config))
            {
                $handler_config['fixed_args'] = Array('plugin', $name);
            }
            else if (! is_array($handler_config['fixed_args']))
            {
                $handler_config['fixed_args'] = Array('plugin', $name, $handler_config['fixed_args']);
            }
            else
            {
                $handler_config['fixed_args'] = array_merge
                (
                    Array('plugin', $name),
                    $handler_config['fixed_args']
                );
            }

            $this->_request_switch["plugin-{$name}-{$identifier}"] = $handler_config;
        }

        return true;
    }

    /**
     * Loads the file/snippet necessary for a given plugin, according to its configuration.
     *
     * @param string $name The plugin name as registered in the plugins configuration
     *     option.
     * @param Array $plugin_config The configuration associated with the plugin.
     * @access private
     * @return boolean Indicating Success
     */
    function _load_nna_plugin_class($name, $plugin_config)
    {
        // Sanity check, we return directly if the configured class name is already
        // available (dynamic_load could trigger this).
        if (class_exists($plugin_config['class']))
        {
            return true;
        }

        if (substr($plugin_config['src'], 0, 5) == 'file:')
        {
            // Load from file
            require(MIDCOM_ROOT . substr($plugin_config['src'], 5));
        }
        else
        {
            // Load from snippet
            mgd_include_snippet_php($plugin_config['src']);
        }

        if (! class_exists($plugin_config['class']))
        {
            return false;
        }

        return true;
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
        */

        if ($_MIDCOM->auth->admin)
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'manage',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('manage teams'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n->get('manage teams'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                )
            );
            /*
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'manage_system',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('manage system'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('manage system'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                )
            );
            */
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
        $this->_request_data['schemadb'] =
            midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));

        $this->_populate_node_toolbar();

        // Load root event or redirect to creation
        if (   !$this->_config->get('teams_root_guid')
            && $handler != 'create-rootgroup')
        {
            $nap = new midcom_helper_nav();
            $node = $nap->get_node($this->_topic->id);

            $_MIDCOM->relocate("{$node[MIDCOM_NAV_FULLURL]}rootgroup/");
            // This will exit
        }

        return true;
    }

    /**
     * This event hook will load any on-site plugin that has been recognized in the configuration.
     * Regardless of success, we always return true; the plugin simply won't start up if, for example,
     * the name is unknown.
     *
     * @access protected
     */
    function _on_can_handle($argc, $argv)
    {
        if (   $argc >= 2
            && $argv[0] == 'plugin')
        {
            /**
             * We do not need to check result of this operation, it populates request switch
             * if successful and does nothing if not, this means normal request handling is enough
             */
            $this->_load_nna_plugin($argv[1]);
        }

        return true;
    }

}

?>