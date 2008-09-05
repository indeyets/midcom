<?php
/**
 * @package midgard.admin.wizards
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which URLs should be handled by this module.
 *
 * @package midgard.admin.wizards
 */
class midgard_admin_wizards_viewer extends midcom_baseclasses_components_request
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

        // Handle /config
        $this->_request_switch['config'] = array
        (
            'handler' => Array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/midgard/admin/wizards/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );

        // Handle /
        $this->_request_switch['index'] = array
        (
            'handler' => Array('midgard_admin_wizards_handler_index', 'index'),
        );

        // Handle / plugin group
        $this->_request_switch['group'] = array
        (
            'handler' => Array('midgard_admin_wizards_handler_index', 'group'),
            'variable_args' => 1,
        );

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
    function _load_nna_plugin($group, $name, $session_id)
    {
        // Validate the plugin name and load the associated configuration
        $groups = $this->_config->get('plugin_groups');

        if (!isset($groups[$group]))
        {
            return false;
        }
        $plugins = $groups[$group]['plugins'];

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

            $handler_config['variable_args'] = 3;
        /*
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
*/
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
     * Populates the node toolbar depending on the user's rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {
/*
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
*/
    }

    /**
     * The handle callback populates the toolbars.
     */
    function _on_handle($handler, $args)
    {
        //$this->_request_data['schemadb'] =
        //    midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));

        $this->_populate_node_toolbar();

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
        $_MIDCOM->auth->require_admin_user();

        if (   $argc = 3 
            && isset($argv[1])
            && isset($argv[2]))
        {
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

            $this->_request_data['current_wizard'] = $argv[0];
            $this->_request_data['current_plugin'] = $argv[1];
            $this->_request_data['session_id'] = $argv[2];

            $groups = $this->_config->get('plugin_groups');
            if (!isset($groups[$argv[0]]))
            {
                /**
                 * NOTE if we return false we trigger 404, but must not try
                 * to proceed further or we cause weird issues for asgard etc
                 */
                return true;
            }
            $plugins = $groups[$argv[0]]['plugins'];
            $plugin_names = array_keys($plugins);

            foreach($plugin_names as $key => $name)
            {
                if ($name == $argv[1])
                {
                    $next_key = $key+1;

                    if (array_key_exists($next_key, $plugin_names))
                    {
                        $this->_request_data['next_plugin'] = $plugin_names[$next_key];
                    }
                    else
                    {
                        $this->_request_data['next_plugin'] = $argv[1];
                    }
                }
            }

            $this->_request_data['next_plugin_full_path'] = $prefix . $argv[0] . "/"
                . $this->_request_data['next_plugin'] ."/" . $argv[2];

            /**
             * We do not need to check result of this operation, it populates request switch
             * if successful and does nothing if not, this means normal request handling is enough
             */
            $this->_load_nna_plugin($argv[0], $argv[1], $argv[2]);
        }

        return true;
    }

    /**
     * Load the site wizard class that ships with Midgard 8.09 onwards.
     */
    public static function load_sitewizard_class(&$data)
    {
        if (   !isset($data['plugin_config']['sitewizard_path'])
            || empty($data['plugin_config']['sitewizard_path']))
        {
            $data['plugin_config']['sitewizard_path'] = '__INSTALL_PREFIX__/share/midgard/setup/php/midgard_admin_sitewizard.php';
        }
        
        $data['plugin_config']['sitewizard_path'] = str_replace('__INSTALL_PREFIX__', $_MIDGARD['config']['prefix'], $data['plugin_config']['sitewizard_path']);
        
        if (!file_exists($data['plugin_config']['sitewizard_path']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Site wizard classes not found from {$data['plugin_config']['sitewizard_path']}");
            // This will exit
        }
        
        require_once($data['plugin_config']['sitewizard_path']);
    }
}

?>