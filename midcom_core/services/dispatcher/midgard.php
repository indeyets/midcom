<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Midgard dispatcher for MidCOM 3
 *
 * Dispatches Midgard HTTP requests to components.
 *
 * @package midcom_core
 */
class midcom_core_services_dispatcher_midgard implements midcom_core_services_dispatcher
{
    public $argv = array();
    public $get = array();
    public $component_name = '';
    public $request_method = 'GET';
    protected $route_array = array();
    protected $route_id = false;
    protected $action_arguments = array();
    protected $route_arguments = array();
    protected $core_routes = array();
    protected $component_routes = array();
    protected $route_definitions = null;
    protected $exceptions_stack = array();

    public function __construct()
    {
        if (isset($_GET))
        {
            $this->get = $_GET;
        }
        
        $this->request_method = $_SERVER['REQUEST_METHOD'];
        
        if (!extension_loaded('midgard'))
        {
            throw new Exception('Midgard 1.x is required for this MidCOM setup.');
        }
        
        /*
        FIXME: For some reason $_MIDGARD['argv'] is broken in 1.9
        if (isset($_MIDGARD['argv']))
        {
            $this->argv = $_MIDGARD['argv'];
        }*/
        $arg_string = substr($_MIDGARD['uri'], strlen($_MIDGARD['self']));
        if ($arg_string)
        {
            $argv = explode('/', $arg_string);
            foreach ($argv as $arg)
            {
                if (empty($arg))
                {
                    continue;
                }
                $this->argv[] = $arg;
            }
        }
    }

    /**
     * Pull data from currently loaded page into the context.
     */
    public function populate_environment_data()
    {
        $page = new midgard_page();
        $page->get_by_id($_MIDGARD['page']);
        
        // Style handling
        $style_id = $_MIDGARD['style'];        
        if ($page->style)
        {
            $style_id = $page->style;
        }
        
        $_MIDCOM->context->page = $page;
        $_MIDCOM->context->style_id = $style_id;
        $_MIDCOM->context->prefix = $_MIDGARD['self'];
        $_MIDCOM->context->uri = $_MIDGARD['uri'];
        $_MIDCOM->context->component = $page->component;
        $_MIDCOM->context->request_method = $this->request_method;
        
        $host = new midgard_host();
        $host->get_by_id($_MIDGARD['host']);
        $_MIDCOM->context->host = $host;        
    }

    /**
     * Generate a valid cache identifier for a context of the current request
     */
    public function generate_request_identifier()
    {
        if (isset($_MIDCOM->context->cache_request_identifier))
        {
            // An injector has generated this already, let it be
            return;
        }

        $identifier_source  = "URI={$_MIDCOM->context->uri}";
        $identifier_source .= ";COMP={$_MIDCOM->context->component}";
        
        // TODO: Check language settings
        $identifier_source .= ';LANG=ALL';
        
        switch ($_MIDCOM->context->cache_strategy)
        {
            case 'public':
                // Shared cache for everybody
                $identifier_source .= ';USER=EVERYONE';
                break;
            default:
                // Per-user cache
                if ($_MIDCOM->authentication->is_user())
                {
                    $user = $_MIDCOM->authentication->get_person();
                    $identifier_source .= ";USER={$user->username}";
                }
                else
                {
                    $identifier_source .= ';USER=ANONYMOUS';
                }
                break;
        }

        $_MIDCOM->context->cache_request_identifier = md5($identifier_source);
    }

    public function initialize($component)
    {
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM dispatcher::initialize');
        }
        
        // In main Midgard request we dispatch the component in connection to a page
        $this->component_name = $component;
        $_MIDCOM->context->component_name = $component;
        $_MIDCOM->context->component_instance = $_MIDCOM->componentloader->load($this->component_name, $_MIDCOM->context->page);
        if ($component == 'midcom_core')
        {
            // MidCOM core templates are already appended
            return;
        }
        $_MIDCOM->templating->append_directory($_MIDCOM->componentloader->component_to_filepath($_MIDCOM->context->component_name) . '/templates');
    }
    
    /**
     * Get route definitions
     */
    public function get_routes()
    {
        $_MIDCOM->context->core_routes = $_MIDCOM->configuration->normalize_routes($_MIDCOM->configuration->get('routes'));
        $_MIDCOM->context->component_routes = array();

        if (   !isset($_MIDCOM->context->component_instance)
            || !$_MIDCOM->context->component_instance)
        {
            return $_MIDCOM->context->core_routes;
        }
        
        $_MIDCOM->context->component_routes = $_MIDCOM->configuration->normalize_routes($_MIDCOM->context->component_instance->configuration->get('routes'));
        
        return array_merge($_MIDCOM->context->component_routes, $_MIDCOM->context->core_routes);
    }


    /**
     * Load a component and dispatch the request to it
     */
    public function dispatch()
    {
        $this->route_definitions = $this->get_routes();
   
        $route_id_map = array();
        foreach ($this->route_definitions as $route_id => $route_configuration)
        {
            if (   isset($route_configuration['root_only'])
                && $route_configuration['root_only'])
            {
                // This route is to be run only with the root page
                if ($_MIDCOM->context->page->id != $_MIDCOM->context->host->root)
                {
                    // We're not in root page, skip
                    continue;
                }
            }
            $route_id_map[] = array
            (
                'route' => $route_configuration['route'],
                'route_id' => $route_id
            );
        }
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM dispatcher::dispatch::routes_fetched');
        }

        unset($route_configuration, $route_id);
        if (!$this->route_matches($route_id_map))
        {
            // TODO: Check message
            throw new midcom_exception_notfound('No route matches current URL');
        }
        unset($route_id_map);
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM dispatcher::dispatch::routes_matched');
        }

        $success_flag = true; // Flag to tell if route ran successfully
        foreach ($this->route_array as $route)
        {
            try
            {   
                $success_flag = true; // before trying route it's marked success
                $this->dispatch_route($route);

                if ($_MIDCOM->timer)
                {
                    $_MIDCOM->timer->setMarker('MidCOM dispatcher::dispatch::dispatched::' . $route);
                }
            }
            catch (Exception $e)
            {
                $this->exceptions_stack[] = $e; // Adding exception to exceptions stack
                $success_flag = false; // route failed
            }
            if ($success_flag) // Checking for success
            {
                break; // if we get here, controller run succesfully so bailing out from the loop
            }
        } // ending foreach

        if (!$success_flag) 
        {
            // if foreach is over and success flag is false throwing exeption
            $messages = '';
            foreach ($this->exceptions_stack as $exception)
            {
                switch (get_class($exception))
                {
                    case 'midcom_exception_unauthorized':
                        throw $exception;
                        // This will exit
                    case 'midcom_exception_httperror':
                        throw $exception;
                        // This will exit
                    default:
                        $messages .= $exception->getMessage() . "\n";
                        break;
                }
            }
            // 404 MultiFail
            throw new midcom_exception_notfound($messages);
        }
    }
    
    private function dispatch_route($route)
    {
        $this->route_id = $route;
        $_MIDCOM->context->route_id = $this->route_id;
        $selected_route_configuration = $this->route_definitions[$this->route_id];
        // Handle allowed HTTP methods
        header('Allow: ' . implode(', ', $selected_route_configuration['allowed_methods']));
        if (!in_array($this->request_method, $selected_route_configuration['allowed_methods']))
        {
            throw new midcom_exception_httperror("{$this->request_method} not allowed", 405);
        }
        
        // Initialize controller
        $controller_class = $selected_route_configuration['controller'];
        $controller = new $controller_class($_MIDCOM->context->component_instance);
        $controller->dispatcher = $this;
    
        // Define the action method for the route_id
        $action_method = "action_{$selected_route_configuration['action']}";
    
        // Handle HTTP request
        if (   $_MIDCOM->configuration->get('enable_webdav')
            && $selected_route_configuration['webdav_only']
            || (   $this->request_method != 'GET'
                && $this->request_method != 'POST')
            )
        {
            // Start the full WebDAV server instance
            $webdav_server = new midcom_core_helpers_webdav($controller);
            $webdav_server->serve($this->route_id, $action_method, $this->action_arguments[$this->route_id]);
            // This will exit
        }
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM dispatcher::dispatch_route::webdav_checked');
        }

        $data = array();

        // Run the route and set appropriate data
        try
        {
            $controller->$action_method($this->route_id, $data, $this->action_arguments[$this->route_id]);
        }
        catch (Exception $e)
        {
            // Read controller's returned data to context before carrying on with exception handling
            $this->data_to_context($selected_route_configuration, $data);
            throw $e;
        }
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM dispatcher::dispatch_route::action_called');
        }
        
        $this->data_to_context($selected_route_configuration, $data);
    }
    
    private function is_core_route($route_id)
    {
        if (!isset($_MIDCOM->context->component_routes))
        {
            return false;
        }
        if (isset($_MIDCOM->context->component_routes[$route_id]))
        {
            return false;
        }
        
        return true;
    }

    private function data_to_context($route_configuration, $data)
    {
        if ($this->is_core_route($this->route_id))
        {
            $_MIDCOM->context->set_item('midcom_core', $data);
        }
        else
        {
            $_MIDCOM->context->set_item($_MIDCOM->context->component_name, $data);
        }
        
        // Set other context data from route
        if (isset($route_configuration['mimetype']))
        {
            $_MIDCOM->context->mimetype = $route_configuration['mimetype'];
        }
        if (isset($route_configuration['template_entry_point']))
        {
            $_MIDCOM->context->template_entry_point = $route_configuration['template_entry_point'];
        }
        if (isset($route_configuration['content_entry_point']))
        {
            $_MIDCOM->context->content_entry_point = $route_configuration['content_entry_point'];
        }
    }

    /**
     * Generates an URL for given route_id with given arguments
     *
     * @param string $route_id the id of the route to generate a link for
     * @param array $args associative arguments array
     * @return string url
     */
    public function generate_url($route_id, array $args, midgard_page $page = null)
    {
        if ( !is_null($page))
        {
            $_MIDCOM->context->create();
            $this->set_page($page);
            $this->initialize($_MIDCOM->context->page->component);
        }
        $route_definitions = $this->get_routes();
        if (!isset($route_definitions[$route_id]))
        {
            throw new OutOfBoundsException("route_id '{$route_id}' not found in routes configuration");
        }
        $route = $route_definitions[$route_id]['route'];
        $link = $route;

        foreach ($args as $key => $value)
        {
            $link = str_replace("{\${$key}}", $value, $link);
        }

        if (preg_match_all('%\{$(.+?)\}%', $link, $link_matches))
        {
            throw new UnexpectedValueException("Missing arguments matching route '{$route_id}' of {$this->component_name}: " . implode(', ', $link_remaining_args));
        }

        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM dispatcher::URL generated::' . $route_id);
        }
    
        if ( !is_null($page))
        {
            $url = preg_replace('%/{2,}%', '/', $this->get_page_prefix() . $link);
            $_MIDCOM->context->delete();
            return $url;
        }

        
        return preg_replace('%/{2,}%', '/', $_MIDCOM->context->prefix . $link);
    }


    /**
     * Tries to match one route from an array of route definitions
     * associated with route_id route_ids
     *
     * The array should look something like this:
     * array
     * (
     *     '/view/{guid:article_id}/' => 'view',
     *     '/?articleid={int:article_id}' => 'view',
     *     '/foo/bar' => 'someroute_id',
     *     '/latest/{string:category}/{int:number}' => 'categorylatest',
     * )
     * The route parts are automatically normalized to end with trailing slash
     * if they don't contain GET arguments
     *
     * @param array $routes map of routes to route_ids
     * @return boolean indicating if a route could be matched or not
     */
    public function route_matches($routes)
    {
        // make a normalized string of $argv
        $argv_str = preg_replace('%/{2,}%', '/', '/' . implode('/', $this->argv) . '/');

        $this->action_arguments = array();
        
//        foreach ($routes as $route => $route_id)
        foreach ($routes as $r)
        {
            $route = $r['route'];
            $route_id = $r['route_id'];
            
            $this->action_arguments[$route_id] = array();
            
            // Reset variables
            list ($route_path, $route_get, $route_args) = $_MIDCOM->configuration->split_route($route);
            
            if (!preg_match_all('%\{\$(.+?)\}%', $route_path, $route_path_matches))
            {
                // Simple route (only static arguments)
                if (   $route_path === $argv_str
                    && (   !$route_get
                        || $this->get_matches($route_get, $route))
                    )
                {
                    // echo "DEBUG: simple match route_id:{$route_id}\n";
                    $this->route_array[] = $route_id;
                }
                if ($route_args) // Route @ set
                {
                    $path = explode('@', $route_path);
                    if (preg_match('%' . str_replace('/', '\/', $path[0]) . '/(.*)\/%', $argv_str, $matches))
                    {
                        $this->route_array[] = $route_id;
                        $this->action_arguments[$route_id]['variable_arguments'] = explode('/', $matches[1]);
                    }
                }
                // Did not match, try next route
                continue;
            }
            // "complex" route (with variable arguments)
            if(preg_match('%@%', $route, $match))
            {   
                $route_path_regex = '%^' . str_replace('%', '\%', preg_replace('%\{(.+?)\}\@%', '([^/]+?)', $route_path)) . '(.*)%';
            }
            else 
            {
                $route_path_regex = '%^' . str_replace('%', '\%', preg_replace('%\{(.+?)\}%', '([^/]+?)', $route_path)) . '$%';
            }
//            echo "DEBUG: route_path_regex:{$route_path_regex} argv_str:{$argv_str}\n";
            if (!preg_match($route_path_regex, $argv_str, $route_path_regex_matches))
            {
                // Does not match, NEXT!
                continue;
            }
            if (   $route_get
                && !$this->get_matches($route_get, $route))
            {
                // We have GET part that could not be matched, NEXT!
                continue;
            }

            // We have a complete match, setup route_id arguments and return
            $this->route_array[] = $route_id;
            // Map variable arguments

            foreach ($route_path_matches[1] as $index => $varname)
            {
                $variable_parts = explode(':', $varname);
                if(count($variable_parts) == 1)
                {
                    $type_hint = '';
                }
                else
                {
                    $type_hint = $variable_parts[0];
                }
                                
                // Strip type hints from variable names
                $varname = preg_replace('/^.+:/', '', $varname);

                if ($type_hint == 'token')
                {
                    // Tokenize the argument to handle resource typing
                    $this->action_arguments[$route_id][$varname] = $this->tokenize_argument($route_path_regex_matches[$index + 1]);
                }
                else
                {
                    $this->action_arguments[$route_id][$varname] = $route_path_regex_matches[$index + 1];
                }
                
                if (preg_match('%@%', $route, $match)) // Route @ set
                {
                    $path = explode('@', $route_path);
                    if (preg_match('%' . str_replace('/', '\/', preg_replace('%\{(.+?)\}%', '([^/]+?)', $path[0])) . '/(.*)\/%', $argv_str, $matches))
                    {
                        $this->route_array[] = $route_id;
                        $this->action_arguments[$route_id] = explode('/', $matches[1]);
                    }
                }
                
            }
            //return true;
        }

        // No match
        if(count($this->route_array) == 0)
        {
            return false;
        }
        return true;
    }
    
    private function tokenize_argument($argument)
    {
        $tokens = array
        (
            'identifier' => '',
            'variant'    => '',
            'language'   => '',
            'type'       => 'html',
        );
        $argument_parts = explode('.', $argument);

        // First part is always identifier
        $tokens['identifier'] = $argument_parts[0];
        
        if (count($argument_parts) >= 2)
        {
            // If there are two or more parts, then second is variant
            $tokens['variant'] = $argument_parts[1];
        }
        
        if (count($argument_parts) >= 3)
        {
            // If there are three parts, then third is type
            $tokens['type'] = $argument_parts[2];
        }

        if (count($argument_parts) >= 4)
        {
            // If there are four or more parts, then third is language and fourth is type
            $tokens['language'] = $argument_parts[2];
            $tokens['type'] = $argument_parts[3];
        }
        
        return $tokens;
    }

    /**
     * Checks GET part of a route definition and places arguments as needed
     *
     * @access private
     * @param string $route_get GET part of a route definition
     * @param string $route full route definition (used only for error reporting)
     * @return boolean indicating match/no match
     *
     * @fixme Move action arguments to subarray
     */
    private function get_matches(&$route_get, &$route)
    {
        /**
         * It's probably faster to check against $route_get before calling this method but
         * we want to be robust
         */
        if (empty($route_get))
        {
            return true;
        }

        if (!preg_match_all('%\&?(.+?)=\{(.+?)\}%', $route_get, $route_get_matches))
        {
            // Can't parse arguments from route_get
            throw new UnexpectedValueException("GET part of route '{$route}' ('{$route_get}') cannot be parsed");
        }

        /*
        echo "DEBUG: route_get_matches\n===\n";
        print_r($route_get_matches);
        echo "===\n";
        */

        foreach ($route_get_matches[1] as $index => $get_key)
        {
            //echo "this->get[{$get_key}]:{$this->get[$get_key]}\n";
            if (   !isset($this->get[$get_key])
                || empty($this->get[$get_key]))
            {
                // required GET parameter not present, return false;
                $this->action_arguments = array();
                return false;
            }
            
            preg_match('%/{\$([a-zA-Z]+):([a-zA-Z]+)}/%', $route_get_matches[2][$index], $matches);
            
            if(count($matches) == 0)
            {
                $type_hint = '';
            }
            else
            {
                $type_hint = $matches[1];
            }
                
            // Strip type hints from variable names
            $varname = preg_replace('/^.+:/', '', $route_get_matches[2][$index]);
                            
            if ($type_hint == 'token')
            {
                 // Tokenize the argument to handle resource typing
                $this->action_arguments[$varname] = $this->tokenize_argument($this->get[$get_key]);
            }
            else
            {
                $this->action_arguments[$varname] = $this->get[$get_key];
            }
        }

        // Unlike in route_matches falling through means match
        return true;
    }
    
    public function set_page(midgard_page $page)
    {
        $_MIDCOM->context->page = $page;
    }
    
    private function get_page_prefix()
    {
        if (!$_MIDCOM->context->page)
        {
            throw new Exception("No page set for the manual dispatcher");
        }
    
        $prefix = "{$_MIDGARD['prefix']}/";
        $host_mc = midgard_host::new_collector('id', $_MIDGARD['host']);
        $host_mc->set_key_property('root');
        $host_mc->execute();
        $roots = $host_mc->list_keys();
        if (!$roots)
        {
            throw new Exception("Failed to load root page data for host {$_MIDGARD['host']}");
        }
        $root_id = null;
        foreach ($roots as $root => $array)
        {
            $root_id = $root;
            break;
        }
        
        if ($_MIDCOM->context->page->id == $root_id)
        {
            return $prefix;
        }
        
        $page_path = '';
        $page_id = $_MIDCOM->context->page->id;
        while (   $page_id
               && $page_id != $root_id)
        {
            $parent_mc = midgard_page::new_collector('id', $page_id);
            $parent_mc->set_key_property('up');
            $parent_mc->add_value_property('name');
            $parent_mc->execute();
            $parents = $parent_mc->list_keys();
            foreach ($parents as $parent => $array)
            {
                $page_id = $parent;
                $page_path = $parent_mc->get_subkey($parent, 'name') . "/{$page_path}";
            }
        }
        
        return $prefix . $page_path;
    }


}
?>