<?php
/**
 * @package net.nehmer.account
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Account Management site interface class
 *
 * This class has a plugin mechanism, which allows you to dynamically add additional,
 * site-specific features to the on-site interface.
 *
 * <b>Plugin API</b>
 *
 * Plugins are basically simple request handler instances which can be configured
 * by the sites administrator in the component config.
 *
 * A valid plugin must consist of a class derived from midcom_baseclasses_components_handler.
 * The only additional rule is that you have to define a static function called
 * get_plugin_handlers(). It must return the information that should be added to the
 * request switch if the plugin is activated:
 *
 * <code>
 * function get_plugin_handlers()
 * {
 *     return Array
 *     (
 *         'index' => Array
 *         (
 *             'handler' => Array('nna_test_plugin_motto', 'motto'),
 *         ),
 *         // ...
 *     );
 * }
 * </code>
 *
 * The entries you return here follow the same rules you already know from the request
 * wide configuration.
 *
 * To actually activate a plugin, a site mainatainer must register it in the components
 * plugin listing. There he must add two pieces of information: The main plugin class (which
 * is used to read the handler list) and an URL to the snippet/file that contains that class.
 * For example, written in the snippet /sitegroup-config/net.nehmer.account/config:
 *
 * <code>
 * 'plugins' => Array
 * (
 *     'motto' => Array
 *     (
 *         'class' => 'nna_test_plugin_motto',
 *         'src' => '/sitegroup-config/net.nehmer.account/test_plugin_motto',
 *         'name' => 'Test Plugin Name',
 *         'config' => null,
 *     ),
 * ),
 * </code>
 *
 * The key of each entry is the name of the plugin, with which it is referenced in the URL
 * of the component. The above example would be .../path/to/account/plugin/motto/...
 * The full URL "space" starting off that point is reserved for the plugin with the above
 * declaration. Note, that the name entry is purely optional, if no name is specified, the
 * plugin identifier is taken instead.
 *
 * This is a little fact important to understand for plugin developers: When you design your
 * plugin, you *always* operate in the namespace assigned to you by the administrator. Your
 * plugin name is only deduced during runtime, not ahead of it. So, for our motto component,
 * the above "index" handler will actually listen to .../account/plugin/motto.html.
 *
 * Each plugin should implement that root page always, as at a later time the plugin system
 * will add the plugins to the NAP leaf information.
 *
 * Additional handlers can be implemented at will, by adding their correpsonding declarations
 * to the request switch. You can even add additional classes, in different files, if you ensure
 * their availablility to the request handler without problems (this currently mostly precludes
 * the use of the autoloading feature, as handlers can only be autoloaded from the midcom
 * installation directory, not from arbitrary locations or snippets. (This might also change in
 * the future.)
 *
 * Site administrators should also be aware of this general workings, so that they can put
 * the plugin into the right "place" in the URL space where they want to have it. By definition,
 * a plugin must be "movable", that is, it must not be tied to a given plugin name. If you need
 * to construct URLs, you should use relative URLs.
 *
 * The 'config' option allows you pass configuration options to the plugin instance. It is
 * optional and defaults to null. The information in this field will be available in the
 * request data entry 'plugin_config'.
 *
 * I'll try to make the current plugin name available in the system somehow as soon as I can to
 * make it easier to write plugins.
 *
 * @todo Factor this out into a pluggable base class with more advanced interfacing.
 * @package net.nehmer.account
 */

class net_nehmer_account_viewer extends midcom_baseclasses_components_request
{
    function net_nehmer_account_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
    }

    function _on_initialize()
    {
        // DM2 configuration screen
        $this->_request_switch['config'] = array
        (
            'handler' => array('net_nehmer_account_handler_configuration', 'configuration'),
            'fixed_args' => array('config'),
        );
        
        // INVITATION
        $this->_request_switch['sent_invites'] = Array
        (
            'handler' => Array('net_nehmer_account_handler_invitation', 'sent_invites'),
            'fixed_args' => Array('sent_invites'),
        );
        $this->_request_switch['invite'] = Array
        (
            'handler' => Array('net_nehmer_account_handler_invitation', 'invite'),
            'fixed_args' => Array('invite'),
        );
        $this->_request_switch['delete_invite'] = Array
        (
            'handler' => Array('net_nehmer_account_handler_invitation', 'delete_invite'),
            'fixed_args' => Array('delete_invite'),
            'variable_args' => 1,
        );
        $this->_request_switch['remind_invite'] = Array
        (
            'handler' => Array('net_nehmer_account_handler_invitation', 'remind_invite'),
            'fixed_args' => Array('remind_invite'),
            'variable_args' => 1,
        );

        // VIEW LINKS
        $this->_request_switch['self'] = Array
        (
            'handler' => Array('net_nehmer_account_handler_view', 'view'),
        );
        $this->_request_switch['self_quick'] = Array
        (
            'handler' => Array('net_nehmer_account_handler_view', 'view'),
            'fixed_args' => Array('quick'),
        );
        $this->_request_switch['other'] = Array
        (
            'handler' => Array('net_nehmer_account_handler_view', 'view'),
            'fixed_args' => 'view',
            'variable_args' => 1,
        );
        $this->_request_switch['other_quick'] = Array
        (
            'handler' => Array('net_nehmer_account_handler_view', 'view'),
            'fixed_args' => Array('view', 'quick'),
            'variable_args' => 1,
        );

        // EDIT LINKS
        $this->_request_switch['edit'] = Array
        (
            'handler' => Array('net_nehmer_account_handler_edit', 'edit'),
            'fixed_args' => Array('edit'),
        );

        if ($this->_config->get('allow_publish'))
        {
            $this->_request_switch['publish'] = Array
            (
                'handler' => Array('net_nehmer_account_handler_publish', 'publish'),
                'fixed_args' => Array('publish'),
            );
            $this->_request_switch['publish_ok'] = Array
            (
                'handler' => Array('net_nehmer_account_handler_publish', 'publish_ok'),
                'fixed_args' => Array('publish', 'ok'),
            );
        }

        $this->_request_switch['password'] = Array
        (
            'handler' => Array('net_nehmer_account_handler_maintain', 'password'),
            'fixed_args' => Array('password'),
        );

        if ($this->_config->get('allow_change_username'))
        {
            $this->_request_switch['username'] = Array
            (
                'handler' => Array('net_nehmer_account_handler_maintain', 'username'),
                'fixed_args' => Array('username'),
            );
        }
        $this->_request_switch['lostpassword'] = Array
        (
            'handler' => Array('net_nehmer_account_handler_maintain', 'lostpassword'),
            'fixed_args' => Array('lostpassword'),
        );

        if ($this->_config->get('allow_cancel_membership'))
        {
            $this->_request_switch['cancel_membership'] = Array
            (
                'handler' => Array('net_nehmer_account_handler_maintain', 'cancel_membership'),
                'fixed_args' => Array('cancel_membership'),
            );
        }

        // ADMIN LINKS
        $this->_request_switch['admin_edit'] = Array
        (
            'handler' => Array('net_nehmer_account_handler_edit', 'edit'),
            'fixed_args' => Array('admin', 'edit'),
            'variable_args' => 1,
        );

//        if ($this->_config->get('allow_register'))
  //      {
            // REGISTRATION LINKS
            $this->_request_switch['register_select_type'] = Array
            (
                'handler' => Array('net_nehmer_account_handler_register', 'select_type'),
                'fixed_args' => Array('register'),
            );
            $this->_request_switch['register'] = Array
            (
                'handler' => Array('net_nehmer_account_handler_register', 'register'),
                'fixed_args' => Array('register'),
                'variable_args' => 1,
            );
            $this->_request_switch['register_activate'] = Array
            (
                'handler' => Array('net_nehmer_account_handler_register', 'activate'),
                'fixed_args' => Array('register', 'activate'),
                'variable_args' => 2,
            );
            $this->_request_switch['register_invitation'] = Array
            (
                'handler' => Array('net_nehmer_account_handler_register', 'register_invitation'),
                'fixed_args' => Array('register_invitation'),
                'variable_args' => 1,
            );
            
            // Pending registrations
            if (   $_MIDCOM->auth->admin
                && $this->_config->get('require_activation'))
            {
                // Match register/pending/
                $this->_request_switch['reqister_list_pending'] = array
                (
                    'handler' => array('net_nehmer_account_handler_pending', 'list'),
                    'fixed_args' => array('pending'),
                );
                
                // Pending registrations
                // Match register/pending/<user guid>/
                $this->_request_switch['reqister_edit_pending'] = array
                (
                    'handler' => array('net_nehmer_account_handler_pending', 'approve'),
                    'fixed_args' => array('pending'),
                    'variable_args' => 1,
                );
            }
    //    }        
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
             * if successfull and does nothing if not, this means normal request handling is enough
             */
            $this->_load_nna_plugin($argv[1]);
        }

        return true;
    }
    
    /**
     * Generic request startup work:
     *
     * - Load the Schema Database
     * - Add the LINK HTML HEAD elements
     * - Populate the Toolbar
     */
    function _on_handle($handler, $args)
    {
        $this->_handler_id = $handler;
        
        $this->_populate_toolbar();
        
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
    function _load_nna_plugin($name)
    {
        // Validate the plugin name and load the associated configuration
        $plugins = $this->_config->get('plugins');
        if (   ! $plugins
            || ! array_key_exists($name, $plugins))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to load the plugin {$name}, no plugins are configured or plugin not activated.");
            debug_pop();
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
     * Prepares the actual plugin by adding all neccessary information to the request
     * switch.
     *
     * @param string $name The plugin name as registered in the plugins configuration
     *     option.
     * @param Array $plugin_config The configuration associated with the plugin.
     * @param Array $handlers The plugin specific handlers without the appropriate prefixes.
     * @access private
     * @return bool Indicating Success
     */
    function _prepare_nna_plugin ($name, $plugin_config, $handlers)
    {
        foreach ($handlers as $identifier => $handler_config)
        {
            // First, update the fixed args list (be tolarent here)
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
     * Loads the file/snippet neccessary for a given plugin, according to its configuration.
     *
     * @param string $name The plugin name as registered in the plugins configuration
     *     option.
     * @param Array $plugin_config The configuration associated with the plugin.
     * @access private
     * @return bool Indicating Success
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
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to load the plugin {$name}, implementation class not available.");
            debug_pop();
            return false;
        }

        return true;
    }   
    
    /**
     * Populates the toolbars depending on the users rights.
     *
     * @access protected
     */
    function _populate_toolbar()
    {
        if ($this->_topic->can_do('midgard:config'))
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'config/',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                )
            );
        }
        
        if ($this->_handler_id === 'config')
        {
            return;
        }
        
        if ($_MIDCOM->auth->user !== null)
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "edit/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit account'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                    MIDCOM_TOOLBAR_ACCESSKEY => 'e',
                )
            );
            
            if ($this->_config->get('allow_publish'))
            {
                $this->_view_toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "publish/",
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('publish account details'),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new_task.png',
                    )
                );
            }

            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "password/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('change password'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/repair.png',
                )
            );
            
            if ($this->_config->get('allow_change_username'))
            {
                $this->_view_toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "username/",
                        MIDCOM_TOOLBAR_LABEL => $this->_config->get('username_is_email') ? 
                            $this->_l10n->get('change email') : $this->_l10n->get('change username'),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/repair.png',
                    )
                );
            }
            
            if ($this->_config->get('allow_cancel_membership'))
            {
                $this->_view_toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "cancel_membership/",
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('cancel membership'),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/cancel.png',
                    )
                );
            }
        }
        
        if ($_MIDCOM->auth->admin)
        {
            $qb = midcom_db_person::new_query_builder();
            
            $qb->begin_group('AND');
                $qb->add_constraint('parameter.domain', '=', 'net.nehmer.account');
                $qb->add_constraint('parameter.name', '=', 'require_approval');
                $qb->add_constraint('parameter.value', '=', 'require_approval');
            $qb->end_group();
            
            // Let the admin user know, if there are pending approvals
            if ($qb->count() > 0)
            {
                $this->_view_toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => 'pending/',
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('pending approvals'),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/not_approved_time_visible.png',
                    )
                );
            }
        }
        
    }

    function verify_person_privileges($person)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $person_user = $_MIDCOM->auth->get_user($person->id);
        
        if (!is_a($person, 'midcom_db_person'))
        {
            $_MIDCOM->auth->request_sudo();
            $person = new midcom_db_person($person->id);
            $_MIDCOM->auth->drop_sudo();
        }
        
        debug_add("Checking privilege midgard:owner for person #{$person->id}");
        
        if (!$_MIDCOM->auth->can_do('midgard:owner', $person, $person_user))
        {
            debug_add("Person #{$person->id} lacks privilege midgard:owner, adding");
            $_MIDCOM->auth->request_sudo();
            if (!$person->set_privilege('midgard:owner', $person_user, MIDCOM_PRIVILEGE_ALLOW))
            {
                debug_add("\$person->set_privilege('midgard:owner', \$person_user, MIDCOM_PRIVILEGE_ALLOW) failed, errstr: " . mgd_errstr(), MIDCOM_LOG_WARN);
            }
            else
            {
                debug_add("Added privilege 'midgard:owner' for person #{$person->id}", MIDCOM_LOG_INFO);
            }
            
            $_MIDCOM->auth->drop_sudo();
        }
        
        debug_pop();
    }

    /**
     * This is a simple function which generates and sends an account registration confirmation
     * including the randomly-generated password and the corresponding activation link.
     *
     * @param midcom_db_person $person  The newly created person account.
     * @param string $password          Password to be included in the message
     * @param activation_link
     * @access static public
     * @todo Make this configurable.
     */
    function send_registration_mail($person, $password, $activation_link)
    {
        $from = $config->get('activation_mail_sender');
        if (! $from)
        {
            $from = $person->email;
        }
        
        $template = array
        (
            'from' => $from,
            'reply-to' => '',
            'cc' => '',
            'bcc' => '',
            'x-mailer' => '',
            'subject' => $this->_l10n->get($config->get('activation_mail_subject')),
            'body' => $this->_l10n->get($config->get('activation_mail_body')),
            'body_mime_type' => 'text/plain',
            'charset' => 'UTF-8',
        );

        $mail = new midcom_helper_mailtemplate($template);
        $parameters = Array
        (
            'PERSON' => $person,
            'PASSWORD' => $password,
            'ACTIVATIONLINK' => $activation_link,
        );
        $mail->set_parameters($parameters);
        $mail->parse();
        return $mail->send($person->email);
    }
}
?>