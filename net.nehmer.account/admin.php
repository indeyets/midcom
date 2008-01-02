<?php
/**
 * @package net.nehmer.account
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Account Manager AIS interface class
 *
 * The AIS system sports the same Plugin system as the live site interface documented
 * in net_nehmer_account_viewer, it just uses the admin_plugins config key.
 *
 * @see net_nehmer_account_viewer
 * @package net.nehmer.account
 */

class net_nehmer_account_admin extends midcom_baseclasses_components_request_admin
{
    function net_nehmer_account_admin($topic, $config)
    {
        parent::midcom_baseclasses_components_request_admin($topic, $config);
    }

    /**
     * @access private
     */
    function _on_initialize()
    {
        // Configuration

        $this->_request_switch['welcome'] = Array
        (
            'handler' => 'welcome',
        );

        $this->_request_switch['config'] = Array
        (
            'handler' => 'config_dm',
            'schemadb' => 'file:/net/nehmer/account/config/schemadb_config.inc',
            'schema' => 'config',
            'disable_return_to_topic' => false,
            'fixed_args' => 'config',
        );

    }

    /**
     * Simple welcome page, lists available plugins. All work done in the show call.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_welcome($handler_id, $args, &$data)
    {
        return true;
    }

    /**
     *
     */
    function _show_welcome($handler_id, &$data)
    {
        echo "<h2>{$this->_topic->extra}</h2>\n";
        echo "<ul>\n";
        echo '<li><a href="config.html">' . $this->_l10n_midcom->get('component configuration') . "</a></li>\n";
        $plugins = $this->_config->get('admin_plugins');
        if ($plugins)
        {
            foreach ($plugins as $identifier => $config)
            {
                $name = array_key_exists('name', $config) ? $config['name'] : $identifier;
                echo "<li><a href='plugin/{$identifier}/'>{$name}</a></li>\n";
            }
        }
        echo "</ul>\n";
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
            $this->_load_plugin($argv[1]);
        }

        return true;
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
    function _load_plugin($name)
    {
        // Validate the plugin name and load the associated configuration
        $plugins = $this->_config->get('admin_plugins');
        if (   ! $plugins
            || ! array_key_exists($name, $plugins))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to load the admin plugin {$name}, no plugins are configured or plugin not activated.");
            debug_pop();
            return;
        }
        $plugin_config = $plugins[$name];

        // Load the plugin class, errors are logged by the callee
        if (! $this->_load_plugin_class($name, $plugin_config))
        {
            return;
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
        if (! $this->_prepare_plugin($name, $plugin_config, $handlers))
        {
            return;
        }
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
     * @return bool Indicating Success
     */
    function _prepare_plugin ($name, $plugin_config, $handlers)
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
     * @return bool Indicating Success
     */
    function _load_plugin_class($name, $plugin_config)
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
            require(substr($plugin_config['src'], 5));
        }
        else
        {
            // Load from snippet
            mgd_include_snippet_php($plugin_config['src']);
        }

        if (! class_exists($plugin_config['class']))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to load the admin plugin {$name}, implementation class not available.");
            debug_pop();
            return false;
        }

        return true;
    }

}

?>
