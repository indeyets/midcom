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
 * Resolver controller and page by path
 * @todo: Lots of refactoring
 * @package midcom_core
 */
class midcom_core_helpers_resolver
{
    private $path;
    private $page_id;
    private $page_obj;
    public $argv = array();
    public $get = array();
    private $component;
    private $component_instance;
    public $component_name = '';
    public $request_method = 'GET';
    private $context = array();
    protected $route_id = false;
    protected $action_arguments = array();
    protected $route_arguments = array();
    protected $core_routes = array();
    protected $component_routes = array();

    public function __construct($path)
    {
        $this->path = $path;
        $this->context = new midcom_core_helpers_context();
        if (isset($_GET))
        {
            $this->get = $_GET;
        }
        
        $this->request_method = $_SERVER['REQUEST_METHOD'];
        
        
        /*
        FIXME: For some reason $_MIDGARD['argv'] is broken in 1.9
        if (isset($_MIDGARD['argv']))
        {
            $this->argv = $_MIDGARD['argv'];
        }*/
        $argv = explode('/', $path);
        foreach ($argv as $arg)
        {
            if (empty($arg))
            {
                continue;
            }
            $this->argv[] = $arg;
        }
    }
    
    
    private function resolve_page($path)
    {
        $temp = trim($path);
        $parent_id = $_MIDCOM->context->host->root;
        $this->page_id = $parent_id;
        $path = explode('/', trim($path));
        foreach($path as $p)
        {
            if(strlen(trim($p)) == 0)
            {                
                continue;
            }
            $qb = new midgard_query_builder('midgard_page');
            $qb->add_constraint('up', '=', $parent_id);
            $qb->add_constraint('name', '=', $p);
            $res = $qb->execute();
            if(count($res) != 1)
            {
                break;            
            }
            $parent_id = $res[0]->id;
            $temp = substr($temp, 1+strlen($p));
            $this->page_id = $parent_id;
        }
        if(strlen($temp)<2)
        {
            $this->path = '/';
        }
        
        return $parent_id;
    }
    

    public function resolve()
    {
        $this->populate_environment_data();
        $this->initialize($this->component);
        return $this->dispatch();
    }
    
    /**
     * Pull data from currently loaded page into the context.
     */
    public function populate_environment_data()
    {
        $page_data = array();
        $mc = midgard_page::new_collector('id', $this->resolve_page($this->path));
        $mc->set_key_property('guid');
        $mc->add_value_property('title');
        $mc->add_value_property('content');
        $mc->add_value_property('component');
        
        $argv = explode('/', $this->path);

        $this->argv = array();
        foreach ($argv as $arg)
        {
            if (empty($arg))
            {
                continue;
            }
            $this->argv[] = $arg;
        }
        
        // Style handling
        $style_id = $_MIDGARD['style'];
        $mc->add_value_property('style');
        
        $mc->execute();
        $guids = $mc->list_keys();
        foreach ($guids as $guid => $array)
        {
            $page_data['id'] = $this->page_id;
            $page_data['guid'] = $guid;
            $page_data['title'] = $mc->get_subkey($guid, 'title');
            $page_data['content'] = $mc->get_subkey($guid, 'content');

            $page_style = $mc->get_subkey($guid, 'style');
            if ($page_style)
            {
                $style_id = $page_style;
            }
            
            $this->component = $mc->get_subkey($guid, 'component');
        }
             
    }
    
    public function initialize($component)
    {
        $page_obj = new midgard_page();
        $page_obj->get_by_id($this->page_id);
        $this->component_name = $component;
        $this->component_instance = $_MIDCOM->componentloader->load($this->component_name, $page_obj);
    }
    
    /**
     * Get route definitions
     */
    public function get_routes()
    {
        $this->core_routes = $_MIDCOM->configuration->normalize_routes($_MIDCOM->configuration->get('routes'));
        
        if (   !isset($this->context->component_instance)
            || !$this->context->component_instance)
        {
            return $this->core_routes;
        }
        
        $this->component_routes = $_MIDCOM->configuration->normalize_routes($this->context->component_instance->configuration->get('routes'));
        
        return array_merge($this->core_routes, $this->component_routes);
    }


    /**
     * Load a component and dispatch the request to it
     */
    public function dispatch()
    {
        $route_definitions = $this->get_routes();

        $route_id_map = array();
        foreach ($route_definitions as $route_id => $route_configuration)
        {
            if (   isset($route_configuration['root_only'])
                && $route_configuration['root_only'])
            {
                // This route is to be run only with the root page
                if ($this->page_id != $_MIDCOM->context->host->root)
                {
                    // We're not in root page, skip
                    continue;
                }
            }
        
            $route_id_map[$route_configuration['route']] = $route_id;
        }
        unset($route_configuration, $route_id);

        if (!$this->route_matches($route_id_map))
        {
            // TODO: Check message
            throw new midcom_exception_notfound('Resolver: No route matches current URL');
        }
        unset($route_id_map);

        $selected_route_configuration = $route_definitions[$this->route_id];
        
        return array('route' => $selected_route_configuration,
                     'route_id' => $this->route_id,
                     'action_arguments' => $this->action_arguments,
                     'page' => new midgard_page($this->page_id));

        // Handle allowed HTTP methods
        header('Allow: ' . implode(', ', $selected_route_configuration['allowed_methods']));
        if (!in_array($this->request_method, $selected_route_configuration['allowed_methods']))
        {
            throw new midcom_exception_httperror("{$this->request_method} not allowed", 405);
        }
        
        // Initialize controller
        $controller_class = $selected_route_configuration['controller'];

        $controller = new $controller_class($this->context->component_instance);
        $controller->dispatcher = $this;
        
        // Define the action method for the route_id
        $action_method = "action_{$selected_route_configuration['action']}";
        
        // Handle HTTP request
        switch ($this->request_method)
        {
            case 'GET':
            case 'POST':
                // Short-cut these types directly to the controller
                break;
            default:
                // For others, start the full WebDAV server instance
                $webdav_server = new midcom_core_helpers_webdav($controller);
                $webdav_server->serve($this->route_id, $action_method, $this->action_arguments);
                // This will exit
        }

        // TODO: store this array somewhere where it can be accessed via get_context_item
        $data = array();
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM dispatcher::dispatch::call action');
        }
        
        // Run the route and set appropriate data
        try
        {
            $controller->$action_method($this->route_id, $data, $this->action_arguments);
        }
        catch (Exception $e)
        {
            // Read controller's returned data to context before carrying on with exception handling
            $this->data_to_context($selected_route_configuration, $data);
            throw $e;
        }
        
        $this->data_to_context($selected_route_configuration, $data);
    }
    
   
    public function get_controller($path)
    {
        $page_id = $this->resolve_page($path);
        $arg = explode('/', $path);
        $argv = array();
        foreach ($arg as $a)
        {
            if (empty($arg))
            {
                    continue;
            }
            $argv[] = $arg;
        }
        unset($arg);
        
        
        
        $page_data = array();
        $mc = midgard_page::new_collector('id', $page_id);
        $mc->set_key_property('guid');
        $mc->add_value_property('title');
        $mc->add_value_property('content');
        $mc->add_value_property('component');
        
        // Style handling
        $style_id = $_MIDGARD['style'];
        $mc->add_value_property('style');
        
        $mc->execute();
        $guids = $mc->list_keys();
        $component = false;
        foreach ($guids as $guid => $array)
        {
            $page_data['id'] = $_MIDGARD['page'];
            $page_data['guid'] = $guid;
            $page_data['title'] = $mc->get_subkey($guid, 'title');
            $page_data['content'] = $mc->get_subkey($guid, 'content');

            $page_style = $mc->get_subkey($guid, 'style');
            if ($page_style)
            {
                $style_id = $page_style;
            }
            
            $component = $mc->get_subkey($guid, 'component');
        }
        
        if (!$component)
        {
            $component = 'midcom_core';
        }
        
        $page = new midgard_page();
        $page->get_by_id($page_id);

        $component_name = $component;
        $component_instance = $_MIDCOM->componentloader->load($component_name, $page);
        // $_MIDCOM->templating->append_directory($_MIDCOM->componentloader->component_to_filepath($this->component_name)
        
        
        // $route_definitions = $this->get_routes();
        
        $core_routes = $_MIDCOM->configuration->normalize_routes($_MIDCOM->configuration->get('routes'));
      
        if (   !isset($component_instance)
            || !$component_instance)
        {
            $component_routes = array();
        }
        else 
        {
            $component_routes = $_MIDCOM->configuration->normalize_routes($component_instance->configuration->get('routes'));
        }
        
        $route_definitions =  array_merge($core_routes, $component_routes);
        
        
        $route_id_map = array();
        foreach ($route_definitions as $route_id => $route_configuration)
        {
            if (   isset($route_configuration['root_only'])
                && $route_configuration['root_only'])
            {   
                // This route is to be run only with the root page
                if ($page_id != $this->context->host->root)
                {
                    // We're not in root page, skip
                    continue;
                }
            }
        
            $route_id_map[$route_configuration['route']] = $route_id;
        }
        
        //unset($route_configuration, $route_id);

      /*  if (!$this->route_matches($route_id_map))
        {
            // TODO: Check message
            throw new midcom_exception_notfound('No route matches current URL');
        }*/
        unset($route_id_map);
        
        
        $selected_route_configuration = $route_definitions[$route_id];
        
        // Initialize controller
        $controller_class = $selected_route_configuration['controller'];
        
        
        return array('route_definitions' => $route_definitions,
                     'controller' => $controller_class,
                     'page' => $page_data
                    );
        
    }
    
    private function is_core_route($route_id)
    {
        if (isset($this->component_routes[$route_id]))
        {
            return false;
        }
        
        return true;
    }

    private function data_to_context($route_configuration, $data)
    {
        if ($this->is_core_route($this->route_id))
        {
            $this->context->set_item('midcom_core', $data);
        }
        else
        {
            $this->context->set_item($this->component_name, $data);
        }
        
        // Set other context data from route
        if (isset($route_configuration['mimetype']))
        {
            $this->context->mimetype = $route_configuration['mimetype'];
        }
        if (isset($route_configuration['template_entry_point']))
        {
            $this->context->template_entry_point = $route_configuration['template_entry_point'];
        }
        if (isset($route_configuration['content_entry_point']))
        {
            $this->context->content_entry_point = $route_configuration['content_entry_point'];
        }
    }

    /**
     * Generates an URL for given route_id with given arguments
     *
     * @param string $route_id the id of the route to generate a link for
     * @param array $args associative arguments array
     * @return string url
     */
    public function generate_url($route_id, array $args)
    {
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
        
        return preg_replace('%/{2,}%', '/', $this->context->prefix . $link);
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
        
        foreach ($routes as $route => $route_id)
        {
            // Reset variables
            $this->action_arguments = array();
            list ($route_path, $route_get, $route_args) = $_MIDCOM->configuration->split_route($route);
            
            if (!preg_match_all('%\{\$(.+?)\}%', $route_path, $route_path_matches))
            {   
                // Simple route (only static arguments)
                if (   $route_path === $argv_str
                    && (   !$route_get
                        || $this->get_matches($route_get, $route))
                    )
                {
                    //echo "DEBUG: simple match route_id:{$route_id}\n";
                    $this->route_id = $route_id;
                    return true;
                };
                if ($route_args) // Route @ set
                {
                    $path = explode('@', $route_path);
                    if (preg_match('%' . str_replace('/', '\/', $path[0]) . '/(.*)\/%', $argv_str, $matches))
                    {
                        $this->route_id = $route_id;
                        $this->action_arguments['variable_arguments'] = explode('/', $matches[1]);
                        return true;
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
            $this->route_id = $route_id;
            // Map variable arguments
            
            foreach ($route_path_matches[1] as $index => $varname)
            {
                preg_match('%/{\$([a-zA-Z]+):([a-zA-Z]+)}/%', $varname, $matches);
                
                if(count($matches) == 0)
                {
                    $type_hint = '';
                }
                else
                {
                    $type_hint = $matches[1];
                }
                                
                // Strip type hints from variable names
                $varname = preg_replace('/^.+:/', '', $varname);

                if ($type_hint == 'token')
                {
                    // Tokenize the argument to handle resource typing
                    $this->action_arguments[$varname] = $this->tokenize_argument($this->get[$get_key]);
                }
                else
                {
                    $this->action_arguments[$varname] = $route_path_regex_matches[$index + 1];
                }
                
                if (preg_match('%@%', $route, $match)) // Route @ set
                {
                    $path = explode('@', $route_path);
                    if (preg_match('%' . str_replace('/', '\/', preg_replace('%\{(.+?)\}%', '([^/]+?)', $path[0])) . '/(.*)\/%', $argv_str, $matches))
                    {
                        $this->route_id = $route_id;
                        $this->action_arguments = explode('/', $matches[1]);
                        return true;
                    }
                }
                
            }
            return true;
        }
        // No match
        return false;
    }
    
    public function get_route_configuration($path)
    {
        
    }

    /**
     * Checks GET part of a route definition and places arguments as needed
     *
     * @access private
     * @param string $route_get GET part of a route definition
     * @param string $route full route definition (used only for error reporting)
     * @return boolean indicating match/no match
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
}
?>