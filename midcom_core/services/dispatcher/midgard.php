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
    protected $route_id = false;
    protected $action_arguments = array();

    public function __construct()
    {
        if (isset($_GET))
        {
            $this->get = $_GET;
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
            $this->argv = explode('/', $arg_string);
        }
    }

    /**
     * Pull data from currently loaded page into the context.
     */
    public function populate_environment_data()
    {
        $page_data = array();
        $mc = midgard_page::new_collector('id', $_MIDGARD['page']);
        $mc->set_key_property('guid');
        $mc->add_value_property('title');
        $mc->add_value_property('content');
        $mc->add_value_property('component');
        
        // Style handling
        $style_id = $_MIDGARD['style'];
        $mc->add_value_property('style');
        
        $mc->execute();
        $guids = $mc->list_keys();
        foreach ($guids as $guid => $array)
        {
            $page_data['guid'] = $guid;
            $page_data['title'] = $mc->get_subkey($guid, 'title');
            $page_data['content'] = $mc->get_subkey($guid, 'content');

            $page_style = $mc->get_subkey($guid, 'style');
            if ($page_style)
            {
                $style_id = $page_style;
            }
            
            $_MIDCOM->context->component = $mc->get_subkey($guid, 'component');
        }
        
        $_MIDCOM->context->page = $page_data;
        $_MIDCOM->context->prefix = $_MIDGARD['self'];
        
        // Append styles from context
        $_MIDCOM->templating->append_style($style_id);
        $_MIDCOM->templating->append_page($_MIDGARD['page']);
    }

    public function initialize($component)
    {
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM dispatcher::initialize');
        }
        
        // In main Midgard request we dispatch the component in connection to a page
        $page = new midgard_page();
        $page->get_by_id($_MIDGARD['page']);
        
        $this->component_name = $component;
        $_MIDCOM->context->component_instance = $_MIDCOM->componentloader->load($this->component_name, $page);
        
        $_MIDCOM->templating->append_directory($_MIDCOM->componentloader->component_to_filepath($this->component_name) . '/templates');
    }
    
    public function get_routes()
    {
        $core_routes = $_MIDCOM->configuration->get('routes');
        
        if (!$_MIDCOM->context->component_instance)
        {
            return $core_routes;
        }
        
        $component_routes = $_MIDCOM->context->component_instance->configuration->get('routes');
        
        return array_merge($core_routes, $component_routes);
    }

    /**
     * Load a component and dispatch the request to it
     */
    public function dispatch()
    {
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM dispatcher::dispatch');
        }
        $route_definitions = $this->get_routes();

        $route_id_map = array();
        foreach ($route_definitions as $route_id => $route_configuration)
        {
            $route_id_map[$route_configuration['route']] = $route_id;
        }
        unset($route_configuration, $route_id);

        if (!$this->route_matches($route_id_map))
        {
            // TODO: Check message
            throw new midcom_exception_notfound('No route matches');
        }
        unset($route_id_map);

        $selected_route_configuration = $route_definitions[$this->route_id];

        $controller_class = $selected_route_configuration['controller'];
        $controller = new $controller_class($_MIDCOM->context->component_instance);
        $controller->dispatcher = $this;
        
        // Then call the route_id
        $action_method = "action_{$selected_route_configuration['action']}";
        // TODO: store this array somewhere where it can be accessed via get_context_item
        $data = array();
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM dispatcher::dispatch::call action');
        }
        $controller->$action_method($this->route_id, $data, $this->action_arguments);
        $_MIDCOM->context->set_item($this->component_name, $data);
        
        // Set other context data from route
        if (isset($selected_route_configuration['mimetype']))
        {
            $_MIDCOM->context->mimetype = $selected_route_configuration['mimetype'];
        }
        if (isset($selected_route_configuration['template_entry_point']))
        {
            $_MIDCOM->context->template_entry_point = $selected_route_configuration['template_entry_point'];
        }
        if (isset($selected_route_configuration['content_entry_point']))
        {
            $_MIDCOM->context->content_entry_point = $selected_route_configuration['content_entry_point'];
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
        $route_definitions = $_MIDCOM->context->component_instance->configuration->get('routes');
        if (!isset($route_definitions[$route_id]))
        {
            throw new OutOfBoundsException("route_id '{$route_id}' not found in routes configuration");
        }
        $route = $route_definitions[$route_id]['route'];
        $link = $route;

        foreach ($args as $key => $value)
        {
            $link = str_replace("{{$key}}", $value, $link);
        }

        if (preg_match_all('%\{(.+?)\}%', $link, $link_matches))
        {
            $link_remaining_args = $link_matches[1];
            throw new UnexpectedValueException('Missing arguments: ' . implode(', ', $link_remaining_args));
        }

        return preg_replace('%/{2,}%', '/', $_MIDCOM->context->prefix . $link);
    }

    /**
     * Normalizes given route definition ready for parsing
     *
     * @param string $route route definition
     * @return string normalized route
     */
    public function normalize_route($route)
    {
        // Normalize route
        if (   strpos($route, '?') === false
            && substr($route, -1, 1) !== '/')
        {
            $route .= '/';
        }
        return preg_replace('%/{2,}%', '/', $route);
    }

    /**
     * Splits a given route (after normalizing it) to it's path and get parts
     *
     * @param string $route reference to a route definition
     * @return array first item is path part, second is get part, both default to boolean false
     */
    public function split_route(&$route)
    {
        $route_path = false;
        $route_get = false;
        $route = $this->normalize_route($route);
        // Get route parts
        $route_parts = explode('?', $route, 2);
        $route_path = $route_parts[0];
        if (isset($route_parts[1]))
        {
            $route_get = $route_parts[1];
        }
        unset($route_parts);
        return array($route_path, $route_get);
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
            list ($route_path, $route_get) = $this->split_route($route);

            //echo "DEBUG: route_id: {$route_id} route:{$route} argv_str:{$argv_str}\n";

            if (!preg_match_all('%\{(.+?)\}%', $route_path, $route_path_matches))
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
                }
                // Did not match, try next route
                continue;
            }
            // "complex" route (with variable arguments)
            $route_path_regex = '%^' . str_replace('%', '\%', preg_replace('%\{(.+?)\}%', '([^/]+?)', $route_path)) . '$%';
            //echo "DEBUG: route_path_regex:{$route_path_regex} argv_str:{$argv_str}\n";
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
                // Strip type hints from variable names
                $varname = preg_replace('/^.+:/', '', $varname);
                $this->action_arguments[$varname] =$route_path_regex_matches[$index+1];
            }
            return true;
        }
        // No match
        return false;
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
            // Strip type hints from variable names
            $varname = preg_replace('/^.+:/', '', $route_get_matches[2][$index]);
            $this->action_arguments[$varname] = $this->get[$get_key];
        }

        // Unlike in route_matches falling through means match
        return true;
    }
}
?>