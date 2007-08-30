<?php

/**
 * @package midcom.baseclasses
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:request.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Base class to encaspulate a request to the component, instantinated by the MidCOM
 * component interface.
 *
 * It provides an automatic mechanism for URL processing and validation, minimizing
 * the required work to get a new component running.
 *
 * <b>Request switch configuration</b>
 *
 * The class uses an array which aids in URL-to-function mapping. Handlers are distinguished
 * by the "URL-space" they handle. For each handler two functions are needed, one for the
 * request handle decision ("Can Handle Phase"), one for the
 * request handling ("Handle Phase") and one for output ("Output Phase"). These handlers can
 * either be contained in this class or refer to another class which gets instantinated, if
 * neccesary.
 *
 * All request handlers are contained in a single array, whose keys identify the various switch
 * configurations. These identifiers are only for informational purposes (they appear in the
 * debug log), so you could just resort to automatic array index numbering using the []
 * operator.
 *
 * Each request handler definition in the switch must contain these key/value pairs:
 *
 * - <b>mixed fixed_args:</b> This is either a string or an array and defines the fixed
 *   arguments that have to be present at the beginning of the URL to be handeld. A
 *   string denotes a single argument, an array is used if more then one fixed argument
 *   is needed. If you do not have any fixed arguments, set this parameter to null, which
 *   is the default.
 * - <b>int variable_args:</b> Usually, there are a number of variables in the URL, like
 *   article IDs, or article names. This can be 0, indicating that no variable arguments are
 *   required, which is the default. For an unlimmited number of variable_args set it to -1. 
 *
 * - <b>bool no_cache:</b> For those cases where you want to prevent a certain "type" of request
 *   being cached. Set to false by default.
 * - <b>int expires:</b> Set the default expiration time of a given type of request. The default -1
 *   is used to indicate no expiration setting. Any positive integer will cause its value to
 *   be passed to the caching engine, indicating the expiration time in seconds.
 * - <b>mixed handler:</b> This is a definition of what method should be invoked to
 *   handle the request. You have two options here. First you can refer to a method of this
 *   request handler class, in that case you just supply the name of the method. Alternativly,
 *   you can refer to an external class for request processing using an array syntax. The
 *   first array member must either contain the name of a existing class or a reference to
 *   an already instantinated class. This value has
 *   no default and must be set. The actual methods called will have either an _handle_ or _show_
 *   prefixed to the exec_handler value, respecitvly. See below for automatic handler instances,
 *   the preferred way to set things up.
 *
 * Example:
 *
 * <code>
 * <?php
 * $this->_request_switch[] = Array
 * (
 *     'fixed_args' => Array ('registratons', 'view'),
 *     'variable_args' => 1,
 *     'no_cache' => false,
 *     'expires' => -1,
 *     'handler' => 'view_registration'
 *     //
 *     // Alternative, use a class with automatic instantination:
 *     // 'handler' => Array('net_nemein_registrations_regadmin', 'view')
 *     //
 *     // Alternative, use existing class (first parameter must be a reference):
 *     // 'handler' => Array(&$regadmin, 'view')
 * );
 * ?>
 * </code>
 *
 * This definition is usually located in either in the _on_initialize event handler (preferred)
 * or the subclass' constructor (discouraged, as you can't use references to $this safely there).
 *
 * The handlers are processed in the order which they have been added to the array. This has
 * several implications:
 *
 * First, if you have two handlers with similar signatures, the latter might be hidden by the
 * former, for example the handler 'view' with two variable arguments includes the urls that
 * could match 'view','registration' with a single variable argument if processed in this order.
 * In these cases you have to add the most specific handlers first.
 *
 * Second, for performance reasons, you should try to add the handler which will be accessed
 * most of the time first (unless it conflicts with the first rule above), as this will speed
 * up average request processing.
 *
 * Subclasses <i>may</i> add additional configuration data to the handler declarations, this
 * is done, for example by the config_dm handler defined in the request_admin subclass. They
 * must only be used to configure predefined requests, you should refer to the documentation
 * of these handlers for details.
 *
 * It is recommended that you add string-based identifiers to your handlers. This makes
 * debugging of URL parsing much easier, as MidCOM logs which request handlers are checked
 * in debug mode. The above example could use something like
 * `$this->_request_switch['registrations-view']` to do so. Just never prefix one of your
 * handlers with one underscores, this namespace is reserved for MidCOM usage.
 *
 * <b>Callback method signatures</b>
 *
 * <code>
 * <?php
 * /**
 *  * can_handle example, with Docblock:
 *  * @param mixed $handler_id The ID of the handler.
 *  * @param Array $args The argument list.
 *  * @param Array $data The local request data.
 *  * @return bool True if the request can be handled, false otherwise.
 *  {@*}
 * function _can_handle_xxx ($handler_id, $args, &$data) {}
 *
 * /**
 *  * Exec handler example, with Docblock:
 *  * @param mixed $handler_id The ID of the handler.
 *  * @param Array $args The argument list.
 *  * @param Array $data The local request data.
 *  * @return bool Indicating success.
 *  {@*}
 * function _handler_xxx ($handler_id, $args, &$data) {}
 *
 * /**
 *  * Show handler example, with Docblock:
 *  * @param mixed $handler_id The ID of the handler.
 *  * @param Array $data The local request data.
 *  * @return bool Indicating success.
 *  {@*}
 * function _show_xxx ($handler_id, &$data) {}
 * ?>
 * </code>
 *
 * The three callbacks match the regular processing sequence of MidCOM.
 *
 * <i>_can_handle_xxx notes:</i> For ease of use,
 * the _can_handle_xxx callback is optional, it will only be called if the method actually
 * exists. Normally you want to override this only if you request handler can hide stuff
 * which is not under the control of your topic. A prominent example is a hander definition
 * which has only a single variable argument. It would hide all subtopics if you don't check
 * what objects actually belong to you, and what not.
 *
 * The main callbacks _handle_xxx and _show_xxx are mandatory.
 *
 * As you can see, the system provides you with an easy way to keep track of the data
 * of your request, without having dozens of members for trivial flags. This data array
 * is automatically registered in the custom component context under the name
 * 'request_data', making it easily available within style elements avoiding the problems
 * of the view_* globals, this is the new recommended
 * way of passing information to the style elements:
 *
 * <code>
 * <?php
 * // Bind the view data, remember the reference assignment:
 * $data =& $_MIDCOM->get_custom_context_data('request_data');
 * ?>
 * </code>
 *
 * The data array can also be accessed by using the $_request_data member of this class,
 * which is the orignal data storage location for the request data.
 *
 * Note, that the request data, for ease of use, already contains references to the L10n
 * Databases of the Component and MidCOM itself located in this class. They are stored
 * as 'l10n' and 'l10n_midcom'. Also availbale as 'config' is the current component
 * configuratio and 'topic' will hold the current conten topic.
 *
 * For those asking about "avoiding the problems of the view_* globals", this basically breaks
 * down to the fact that multiple components use these variables simultaneously. If you
 * invoke a dynamic_load within a style elemnt, you have good chances, that after it your
 * original global $view variables have been overwritten by the style code of the
 * dynamically loaded component.
 *
 *
 * <b>Automatic handler class instantination</b>
 *
 * If you specify a class name instead of a class isntance as a exec handler, MidCOM will
 * automatically create an instance of that class type and initialize it. These
 * so-called handler classes must be a subclass of midcom_baseclasses_components_handler.
 *
 * The subclasses you create should look about this:
 *
 * <code>
 * <?php
 * class my_handler extends midcom_baseclasses_components_handler
 * {
 *     function my_handler()
 *     {
 *         // just call the base class constructor, avoid
 *         // additional code at this point.
 *         parent::midcom_baseclasses_components_handler();
 *     }
 *
 *     function _on_initialize()
 *     {
 *         // Add class initialization code here, all members have
 *         // been prepared, and the instance is already stable, so
 *         // you can safely work with references to $this here.
 *     }
 * }
 * ?>
 * </code>
 *
 * The two methods for each handler have the same signature as if they were in the
 * same class.
 *
 *
 * <b>Plugin Interface</b>
 *
 * This class includes a plugin system which can be used to flexibly enhance the
 * functionality of the request classes by external sources. Your component does
 * not have to worry about this, you just have to provide a way to register plugins
 * to site authors.
 *
 * Plugins always come in "packages", which are assigned to a namespace. The namespace
 * is used to separate various plugins from each other, it is prepended before any
 * URL. Within a plugin you can register one or more handler classes. Each of this
 * classes can of course define more then one request handler.
 *
 * A plugin class must be a decendant of Midcom_baseclasses_components_handler or at
 * least support its full interface.
 *
 * It must define an additional function, called get_plugin_handlers(). It has to return
 * an array of standard request handler declarations. Both handler identifiers and
 * argument lists are <em>relative</em> to the base URL of the plugin (see below),
 * not to the component running the problem. You are thus completly location independant.
 * The handler callback must be statically callable.
 *
 * <em>Example: Plugin handler callback</em>
 *
 * <code>
 * function get_plugin_handlers()
 * {
 *     return Array
 *     (
 *         'create' => Array
 *         (
 *             'handler' => Array('midcom_admin_content_topic_plugin', 'create'),
 *             'fixed_args' => 'create',
 *             // 'variable_args' => 1,
 *         ),
 *         // ...
 *     );
 * }
 * </code>
 *
 * As outlined above, plugins are managed in a two-level hierarchy. First, there is
 * the plugin identifier, second the class identifier. When registering a plugin,
 * these two are specified. The request handlers obtained by the above callback are
 * automatically expanded to match the plugin namespace.
 *
 * <em>Example: Plugin registration</em>
 *
 * <code>
 * $this->register_plugin_namespace
 * (
 *     '__ais',
 *     Array
 *     (
 *         'topic' => Array
 *         (
 *             'class' => 'midcom_admin_content_topic_plugin',
 *             'src' => 'file:/midcom/admin/content/_topic_plugin.php',
 *             'name' => 'Topic administration',
 *             'config' => null,
 *         ),
 *     )
 * );
 * </code>
 *
 * The first argument of this call identifies the plugin namespace, the second
 * the list of classes associated with this plugin. Each class gets its own
 * identifier. The namespace and class identifier is used to construct the
 * final plugin URL: {$anchor_prefix}/{$namespace}/{$class_identifier}/...
 * This gives fully unique URL namespaces to all registered plugins.
 *
 * Plugin handlers always last in queue, so they won't override component handlers.
 * Their name is prefixed with __{$namespace}-{$class_identifier} to ensure
 * uniqueness.
 *
 * Each class must have these options:
 *
 * - class: The name of the class to use
 * - src: The source URL of the plugin class. This can be either a file:/...
 *   URL which is relative to MIDCOM_ROOT, snippet:/... which identifies an
 *   arbitrary snippet loaded with mgd_include_snippet or, finally, component:...
 *   which will load the component specified. This is only used if the class
 *   is not yet available.
 * - name: This is the clear-text name of the plugin.
 * - config: This is an optional configuration argument, allows for customization.
 *   May be omitted, in which case it defaulst to null.
 *
 * Once a plugin has been successfully initialized, its configuration is put
 * into the request data:
 *
 * - mixed plugin_config: The configuration passed to the plugin as outlined
 *   above.
 * - string plugin_name: The name of the plugin as defined in its config
 * - string plugin_namespace: The plugin namespace defined when registering.
 * - string plugin_anchorprefix: A plugin-aware version of
 *   MIDCOM_CONTEXT_ANCHORPREFIX pointing to the root URL of the plugin.
 *
 * @package midcom.baseclasses
 */
class midcom_baseclasses_components_request extends midcom_baseclasses_core_object
{
    /**#@+
     * Request state variable, set during startup. There should be no need to change it
     * in most cases.
     *
     * @access public
     */

    /**
     * The topic for which we are handling a requiest.
     *
     * @var midcom_db_topic
     */
    public $_topic = null;

    /**
     * The current configuration.
     *
     * @var midcom_helper_configuration
     */
    public $_config = null;

    /**
     * A handle to the i18n service.
     *
     * @var midcom_helper_services_i18n
     */
    public $_i18n = null;

    /**
     * The components' L10n string database
     *
     * @var midcom_services__i18n_l10n
     */
    public $_l10n = null;

    /**
     * The global MidCOM string database
     *
     * @var midcom_services__i18n_l10n
     */
    public $_l10n_midcom = null;

    /**
     * Component data storage area.
     *
     * @var Array
     */
    public $_component_data = null;

    /**
     * Request specific data storage area. Registered in the component context
     * as ''.
     *
     * @var Array
     */
    public $_request_data = Array();

    /**
     * Internal helper, holds the name of the component. Should be used whenever the
     * components' name is required instead of hardcoding it.
     *
     * @var string
     */
    public $_component = '';

    /**
     * The node toolbar for the current request context. Not available during the can_handle
     * phase.
     *
     * @var midcom_helper_toolbar
     * @see midcom_services_toolbars
     */
    public $_node_toolbar = null;

    /**
     * The view toolbar for the current request context. Not available during the can_handle
     * phase.
     *
     * @var midcom_helper_toolbar
     * @see midcom_services_toolbars
     */
    public $_view_toolbar = null;

    /**
     * This variable keeps track of the registered plugin namespaces. It maps namespace
     * identifiers against plugin config lists. This is used during can_handle startup
     * to determine whether the request has to be relayed to a plugin.
     *
     * You have to use the register_plugin_namespace() member function during the
     * _on_initialize event to register plugin namespaces.
     *
     * @var array
     */
    public $_plugin_namespace_config = Array();

    /**#@-*/

    /**
     * Request execution switch configuration.
     *
     * The main request switch data. You need to set this during construction,
     * it will be post-processed afterwards during initialize to provide a unified
     * set of data. Therefore you must not modify this switch after construction.
     *
     * @access public
     * @var Array
     */
    public $_request_switch = Array();

    /**#@+
     * Internal request handling state variable, these are considered read-only for
     * all purposes (except this base class).
     *
     * @access public
     */

    /**
     * This is a reference to the handler which declared to be able to handle the
     * request. The array will contain the original index of the handler in the
     * 'id' member for backtracking purposes. The variable argument list will be
     * placed into 'args' for performance reasons.
     *
     * @var Array
     */
    public $_handler = null;

    /**
     * The url to the css file to be added when onsite toolbars are shown.
     * @var string
     * @access public
     */
    public $_onsite_toolbar_css = null;

    /**#@-*/

    /**
     * Initializes the class, only basic variable assignement. Your own constructor
     * should call this function first.
     *
     * Note, that it is recommended to put all further initialization work into
     * the _on_initialize event handler.
     *
     * @param MidgardTopic $topic The topic we are working on
     * @param midcom_helper_configuration $config The currently active configuration.
     */
    public function midcom_baseclasses_components_request($topic, $config)
    {
        if (! $_MIDCOM->dbclassloader->is_midcom_db_object($topic))
        {
            $this->_topic = $_MIDCOM->dbfactory->convert_midgard_to_midcom($topic);
        }
        else
        {
            $this->_topic = $topic;
        }
        $this->_config = $config;
    }


    /**
     * Initializes the request handler class, called by the component interface after
     * instantination. Required to allow safe $this references during startup.
     *
     * @param string $component The name of the component.
     */
    public function initialize($component)
    {
        $this->_component = $component;
        $this->_component_data =& $GLOBALS['midcom_component_data'][$this->_component];
        $_MIDCOM->set_custom_context_data('request_data', $this->_request_data);

        $this->_i18n =& $_MIDCOM->get_service('i18n');
        $this->_l10n =& $this->_i18n->get_l10n($this->_component);
        $this->_l10n_midcom =& $this->_i18n->get_l10n('midcom');

        $this->_request_data['config'] =& $this->_config;
        $this->_request_data['topic'] = null;
        $this->_request_data['l10n'] =& $this->_l10n;
        $this->_request_data['l10n_midcom'] =& $this->_l10n_midcom;

        $this->_register_core_plugin_namespaces();


        $this->_on_initialize();

    }


    /**
     * This public helper post-processes the initial information as set by the constructor.
     * It fills all missing fields with sensible defaults, see the class introdction for
     * deatils.
     */
    public function _prepare_request_switch()
    {
        foreach ($this->_request_switch as $key => $value)
        {
            if (   ! array_key_exists('fixed_args', $value)
                || is_null($value['fixed_args']))
            {
                $this->_request_switch[$key]['fixed_args'] = Array();
            }
            else if (is_string($value['fixed_args']))
            {
                $this->_request_switch[$key]['fixed_args'] = Array($value['fixed_args']);
            }

            if (! array_key_exists('variable_args', $value))
            {
                $this->_request_switch[$key]['variable_args'] = 0;
            }

            if (is_string($value['handler']))
            {
                $this->_request_switch[$key]['handler'] = Array(&$this, $value['handler']);
            }

            if (   ! array_key_exists('expires', $value)
                || ! is_integer($value['expires'])
                || $value['expires'] < -1)
            {
                $this->_request_switch[$key]['expires'] = -1;
            }
            if (! array_key_exists('no_cache', $value))
            {
                $this->_request_switch[$key]['no_cache'] = false;
            }
        }
    }


    /**
     * CAN_HANDLE Phase interface, checks against all registered handlers if a vaild
     * one can be found. You should not need to override this, instead, use the
     * HANDLE Phase for further checks.
     *
     * If available, the function calls the _can_handle callback of the eventhandlers
     * which potentially match the argument declaration.
     *
     * @param int $argc The argument count
     * @param Array $argv The argument list
     * @return bool Indicating wether the request can be handled by the class, or not.
     */
    public function can_handle($argc, $argv)
    {
        // Call the general can_handle event handler
        $result = $this->_on_can_handle($argc, $argv);
        if (! $result)
        {
            debug_push_class($this, 'can_handle');
            debug_add('The _on_can_handle event handler returned false, aborting.');
            debug_pop();
            return false;
        }

        // Check if we need to start up a plugin.
        if (   $argc > 1
            && array_key_exists($argv[0], $this->_plugin_namespace_config)
            && array_key_exists($argv[1], $this->_plugin_namespace_config[$argv[0]]))
        {
            $namespace = $argv[0];
            $plugin = $argv[1];
            debug_push_class($this, 'can_handle');
            debug_add("Loading the plugin {$namespace}/{$plugin}");
            debug_pop();
            $this->_load_plugin($namespace, $plugin);
        }

        $this->_prepare_request_switch();

        foreach ($this->_request_switch as $key => $request)
        {   
            $fixed_args_count = count($request['fixed_args']);
            $total_args_count = $fixed_args_count + $request['variable_args'];

            if (( $argc != $total_args_count && (  $request['variable_args'] >= 0 )) || $fixed_args_count > $argc)
            {
                continue;
            }

            // Check the static parts
            for ($i = 0; $i < $fixed_args_count; $i++)
            {
                if ($argv[$i] != $request['fixed_args'][$i])
                {
                    continue 2;
                }
            }
            
            // We have a match.
            $this->_handler =& $this->_request_switch[$key];
            $this->_handler['id'] = $key;
            $this->_handler['args'] = array_slice($argv, $fixed_args_count);

            // Prepare the handler object
            $this->_prepare_handler();

            // If applicable, run the _can_handle check for the handler in question.
            $handler =& $this->_handler['handler'][0];
            $method = "_can_handle_{$this->_handler['handler'][1]}";

            if (method_exists($handler, $method))
            {
                $result = $handler->$method($this->_handler['id'], $this->_handler['args'], $this->_request_data);
                if ($result)
                {
                    return true;
                }
                else
                {
                    debug_push_class($this, 'can_handle');
                    debug_add("Handler method {$method} returned FALSE, we cannot handle this therefore.");
                    debug_pop();
                    return false;
                }
            }
            else
            {
                return true;
            }
        }

        // No match
        debug_push_class($this, 'can_handle');
        debug_add('No matching handler could be found, we cannot handle this therefore.');
        debug_pop();
        return false;
    }


    /**
     * This method handles the request using the handler determined by the can_handle
     * check.
     *
     * Before doing anything, it will call the _on_handle event handler to allow for
     * generic request preparation.
     *
     * @param int $argc The argument count
     * @param Array $argv The argument list
     * @return bool Indicating wether the request was handled successfully.
     * @see _on_handle();
     */
    public function handle($argc, $argv)
    {
        // Init
        $handler =& $this->_handler['handler'][0];

        // Update the request data
        $this->_request_data['topic'] =& $this->_topic;
        if (array_key_exists('plugin_namespace', $this->_request_data))
        {
            // Prepend the plugin anchor prefix so that it is complete.
            $this->_request_data['plugin_anchorprefix'] =
                  $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                . $this->_request_data['plugin_anchorprefix'];
        }

        // Get the toolbars for both the main request object and the handler
        // objcet. Note, if both are equal, we will have two assignments at this
        // point; it shouldn't bother us, it isn't a regular use-case anymore (besides
        // the fact that this is only a very very very minor performance issue).
        $this->_node_toolbar =& $_MIDCOM->toolbars->get_node_toolbar();
        $this->_view_toolbar =& $_MIDCOM->toolbars->get_view_toolbar();
        $handler->_node_toolbar =& $_MIDCOM->toolbars->get_node_toolbar();
        $handler->_view_toolbar =& $_MIDCOM->toolbars->get_view_toolbar();

        // Call the general handle event handler
        $result = $this->_on_handle($this->_handler['id'], $this->_handler['args']);
        if (! $result)
        {
            debug_push_class($this, 'handle');
            debug_add('The _on_handle event handler returned false, aborting.');
            debug_pop();
            return false;
        }

        $method = "_handler_{$this->_handler['handler'][1]}";
        $result = $handler->$method($this->_handler['id'], $this->_handler['args'], $this->_request_data);
        if ($result == false)
        {
            // Default the error code to ERRCRIT (HTTP Error 500)
            $this->errcode = MIDCOM_ERRCRIT;
        }

        // Check wether this request should not be cached by default:
        if ($this->_handler['no_cache'] == true)
        {
            $_MIDCOM->cache->content->no_cache();
        }
        if ($this->_handler['expires'] >= 0)
        {
            $_MIDCOM->cache->content->expires($this->_handler['expires']);
        }

        return $result;
    }

    /**
     * Helper function, which prepares the handler callback for execution.
     * This will create the handler class instance if required.
     *
     * @access public
     */
    public function _prepare_handler()
    {
        if (is_string($this->_handler['handler'][0]))
        {
            $classname = $this->_handler['handler'][0];
            if (! $this->_verify_handler_class($classname))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Failed to create a class instance of the type {$classname}, the class is not declared.");
                // This will exit
            }

            $this->_handler['handler'][0] = new $classname();
            if (! is_a($this->_handler['handler'][0], 'midcom_baseclasses_components_handler'))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Failed to create a class instance of the type {$classname}, it is no subclass of midcom_baseclasses_components_handler.");
                // This will exit
            }

            $this->_handler['handler'][0]->initialize($this);
        }
    }

    /**
     * This is an helper function used during handler startup. It ensures that handler class is
     * loaded. It has auto-class-loading support, which allows the component author to have
     * the handler classes only loaded on demand (see class introduction).
     *
     * @param string $classname The name of the handler to validate.
     * @return bool Indicating success.
     */
    public function _verify_handler_class($classname)
    {
        if (class_exists($classname))
        {
            return true;
        }

        // Try auto-load.
        $path = MIDCOM_ROOT . '/' . str_replace('_', '/', $classname) . '.php';
        if (! file_exists($path))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Auto-loading of the class {$classname} from {$path} failed: File does not exist.", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        require_once($path);
        if (! class_exists($classname))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to load the handler {$classname} from file {$path}: Handler class is not declared.", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        return true;

    }

    /**
     * Display the content, it uses the handler as determined by can_handle.
     *
     * Before doing anything, it will call the _on_showent handler to allow for
     * generic preparation. If this function returns false, the regular output
     * handler will not be called.
     *
     * @see _on_show();
     */
    public function show()
    {
         debug_push_class($this, 'show');

        // Call the event handler
        $result = $this->_on_show($this->_handler['id']);
        if (! $result)
        {
            debug_add('The _on_show event handler returned false, aborting.');
            debug_pop();
            return;
        }

        // Call the handler:
        $handler =& $this->_handler['handler'][0];
        $method = "_show_{$this->_handler['handler'][1]}";

        $handler->$method($this->_handler['id'], $this->_request_data);

        debug_pop();
    }


    /**#@+
     * Event Handler callback.
     */

    /**
     * Initialization event handler, called at the end of the initialization process
     * immediately before the request handler configuration is read.
     *
     * Use this function instead of the constructor for all initialization work, as
     * it makes your life much easier with references to $this being available. You
     * can safely populate the request switch from here.
     *
     * You should not do anything else then general startup work, as this callback
     * executes <em>before</em> the can_handle phase. You don't know at this point
     * wether you are even able to handle the request. Thus, anything that is specific
     * to your request (like HTML HEAD tag adds) must not be done here. Use _on_handle
     * instead.
     */
    public function _on_initialize()
    {
        return;
    }

    /**
     * Component specific initialization code for the handle phase. AIS, for example, uses
     * this to preapre the toolbar arrays. The name of the request handler is passed as an
     * argumet to the event handler.
     *
     * If you discover that you cannot handle the request already at this stage, return false
     * and set the error variables accordingly. The reminder of the handle phase is skipped
     * then, returning immediately to MidCOM.
     *
     * Note, that while you have the complete information around the request (handler id,
     * args and request data) available, it is strongly discouraged to handle everything
     * here. Instead, stay with the specific request handler methods as far as sensible.
     *
     * This callback is executed even if the actual request is handled by an external
     * handler class.
     *
     * @param mixed $handler The ID (Array-Key) of the handler that is responsible to handle
     *   the request.
     * @param Array $args The argument list.
     * @return bool Return false to abort the handle phase, true to continue normally.
     */
    public function _on_handle($handler, $args)
    {
        return true;
    }

    /**
     * Component specific initialization code for the can_handle phase.
     *
     * This is run before the actual evaluation of the request switch. Components can use
     * this phase to load plugins that need registering in the request switch on demand.
     *
     * The advantage of this is that it is not neccessary, to load all plugins completly,
     * you just have to know the "root" URL space (f.x. "/plugins/$name/").
     *
     * If you discover that you cannot handle the request already at this stage, return false
     * The reminder of the can_handle phase is skipped then, returning the URL processing back
     * to MidCOM.
     *
     * @param int $argc The argument count as passed by the Core.
     * @param Array $argv The argument list.
     * @return bool Return false to abort the handle phase, true to continue normally.
     */
    public function _on_can_handle($argc, $argv)
    {
        return true;
    }

    /**
     * Generic output initialization code. The return value lets you control wether the
     * output method associated with the handler declaration is called, return false to
     * override this automatism, true, the default, will call the output handler normally.
     *
     * @param mixed $handler The ID (Array-Key) of the handler that is responsible to handle
     *   the request.
     * @return bool Return false to override the regular component output.
     */
    public function _on_show($handler)
    {
        return true;
    }

    /**#@-*/

    /**
     * This function creates a new plugin namespace and maps the configuration to it.
     * It allows flexible, user-configurable extension of components.
     *
     * Only very basic testing is done to keep runtime up, currently the system only
     * checks to prevent duplicate namespace registrations. In such a case,
     * generate_error will be called. Any further validation won't be done before
     * can_handle determines that a plugin is actually in use.
     *
     * @param string $namespace The plugin namespace, checked against $args[0] during
     *     URL parsing.
     * @param Array $config The configuration of the plugin namespace as outlined in
     *     the class introduction
     */
    public function register_plugin_namespace($namespace, $config)
    {
        if (array_key_exists($namespace, $this->_plugin_namespace_config))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Tried to register the plugin namespace {$namespace}, but it is already registered.");
            // This will exit
        }
        $this->_plugin_namespace_config[$namespace] = $config;
    }

    /**
     * This helper loads the specified namespace/plugin combo.
     *
     * Any problem to load a plugin will be logged accordingly and false will be returned.
     * Critical errors will trigger generate_error.
     *
     * @todo Allow for lazy plugin namespace configuraiton loading (using a callback)!
     *     This will make things more performant and integration with other components
     *     much easier.
     *
     * @param string $namespace The plugin namespace to use.
     * @param string $plugin The plugin to load from the namespace.
     * @return bool Indicating success
     */
    public function _load_plugin($namespace, $plugin)
    {
        if (! $this->_load_plugin_class($namespace, $plugin))
        {
            return false;
        }

        $plugin_config = $this->_plugin_namespace_config[$namespace][$plugin];

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
        $this->_request_data['plugin_name'] = $plugin;
        $this->_request_data['plugin_namespace'] = $namespace;

        // This cannot be fully prepared at this point, as the ANCHORPREFIX is
        // undefined up until to the handle phase. It thus completed in handle
        // by prefixing the local anchorprefix.
        $this->_request_data['plugin_anchorprefix'] = "{$namespace}/{$plugin}/";


        // Load remaining configuration, and prepare the plugin,
        // errors are logged by the callers.
        $handlers = call_user_func(array($plugin_config['class'], 'get_plugin_handlers'));

        return $this->_prepare_plugin($namespace, $plugin, $handlers);
    }

    /**
     * Loads the file/snippet neccessary for a given plugin, according to its configuration.
     *
     * @param string $namespace The plugin namespace to use.
     * @param string $plugin The plugin to load from the namespace.
     * @access public
     * @return bool Indicating Success
     */
    public function _load_plugin_class($namespace, $plugin)
    {
        $plugin_config = $this->_plugin_namespace_config[$namespace][$plugin];

        // Sanity check, we return directly if the configured class name is already
        // available (dynamic_load could trigger this).
        if (class_exists($plugin_config['class']))
        {
            return true;
        }

        $i = strpos($plugin_config['src'], ':');
        if ($i == false)
        {
            $method = 'snippet';
            $src = $plugin_config['src'];
        }
        else
        {
            $method = substr($plugin_config['src'], 0, $i);
            $src = substr($plugin_config['src'], $i + 1);
        }

        switch($method)
        {
            case 'file':
                require_once(MIDCOM_ROOT . $src);
                break;

            case 'component':
                $_MIDCOM->componentloader->load($src);
                break;

            case 'snippet':
                mgd_include_snippet_php($src);
                break;

            default:
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "The plugin loader method {$method} is unknown, cannot continue.");
                // This will exit().
        }

        if (! class_exists($plugin_config['class']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to load the plugin {$namespace}/{$plugin}, implementation class not available.");
            // This will exit.
        }

        return true;
    }

    /**
     * Prepares the actual plugin by adding all neccessary information to the request
     * switch.
     *
     * @param string $namespace The plugin namespace to use.
     * @param string $plugin The plugin to load from the namespace.
     * @param Array $handlers The plugin specific handlers without the appropriate prefixes.
     * @access public
     * @return bool Indicating Success
     */
    public function _prepare_plugin ($namespace, $plugin, $handlers)
    {
        $plugin_config = $this->_plugin_namespace_config[$namespace][$plugin];

        foreach ($handlers as $identifier => $handler_config)
        {
            // First, update the fixed args list (be tolarent here)
            if (! array_key_exists('fixed_args', $handler_config))
            {
                $handler_config['fixed_args'] = Array($namespace, $plugin);
            }
            else if (! is_array($handler_config['fixed_args']))
            {
                $handler_config['fixed_args'] = Array($namespace, $plugin, $handler_config['fixed_args']);
            }
            else
            {
                $handler_config['fixed_args'] = array_merge
                (
                    Array($namespace, $plugin),
                    $handler_config['fixed_args']
                );
            }

            $this->_request_switch["__{$namespace}-{$plugin}-{$identifier}"] = $handler_config;
        }
        return true;
    }

    /**
     * This helper function registers the plugin namespaces provided from the MidCOM
     * core.
     *
     * This is an intermediate implementation providing simple topic management operations
     * and is to be considered proof-of-concept. Full integration into the Aegir 2 framework
     * is pending.
     */
    public function _register_core_plugin_namespaces()
    {
        $this->register_plugin_namespace
        (
            '__ais',
            Array
            (
                'folder' => Array
                (
                    'class' => 'midcom_admin_folder_folder_management',
                    'src' => 'file:/midcom/admin/folder/folder_management.php',
                    'name' => 'Folder administration',
                    'config' => null,
                ),
                'acl' => Array
                (
                    'class' => 'midgard_admin_acl_editor_plugin',
                    'src' => 'file:/midgard/admin/acl/acl_editor.php',
                    'name' => 'Privileges',
                    'config' => null,
                ),
                'rcs' => Array
                (
                    'class' => 'no_bergfald_rcs_handler',
                    'src' => 'file:/no/bergfald/rcs/handler.php',
                    'name' => 'Revision control',
                    'config' => null,
                ),
                'imagepopup' => Array
                (
                    'class' => 'midcom_helper_imagepopup_viewer',
                    'src' => 'file:/midcom/helper/imagepopup/viewer.php',
                    'name' => 'Image pop-up',
                    'config' => null,
                ),
                'midcom-settings' => array
                (
            	    'class' => 'midcom_admin_settings_editor',
            	    'src' => 'file:/midcom/admin/settings/editor.php',
            	    'name' => 'MidCOM site configuration',
            	    'config' => null,
            	),
            	'l10n' => array
            	(
            	    'class' => 'midcom_admin_babel_main',
            	    'src' => 'file:/midcom/admin/babel/main.php',
            	    'name' => 'MidCOM localization',
            	    'config' => null,
            	),
            	'help' => array
            	(
            	    'class' => 'midcom_admin_help_help',
            	    'src' => 'file:/midcom/admin/help/help.php',
            	    'name' => 'On-site help',
            	    'config' => null,
            	),
            )
        );
        
        // Centralized admin panel functionalities
        
        // Load plugins registered via component manifests
        $manifest_plugins = array();
        $customdata = $_MIDCOM->componentloader->get_all_manifest_customdata('request_handler_plugin');
        foreach ($customdata as $component => $plugin_config)
        {
            $manifest_plugins[$component] = $plugin_config;
        }
        $customdata = $_MIDCOM->componentloader->get_all_manifest_customdata('asgard_plugin');
        foreach ($customdata as $component => $plugin_config)
        {
            $manifest_plugins["asgard_{$component}"] = $plugin_config;
        }
        
        $hardcoded_plugins = array
        (
            'asgard' => array
            (
                'class' => 'midgard_admin_asgard_plugin',
                'src' => 'file:/midgard/admin/asgard/plugin.php',
                'name' => 'Asgard',
                'config' => null,
            ),
        );

        $this->register_plugin_namespace
        (
            '__mfa',
            array_merge($hardcoded_plugins, $manifest_plugins)
        );
    }

}

?>
