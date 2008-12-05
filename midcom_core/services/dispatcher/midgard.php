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
        $this->argv = explode('/', $arg_string);
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
        $mc->add_value_property('component');
        
        $mc->execute();
        $guids = $mc->list_keys();
        foreach ($guids as $guid => $array)
        {
            $page_data['guid'] = $guid;
            $page_data['title'] = $mc->get_subkey($guid, 'title');
            $_MIDCOM->set_context_item('component', $mc->get_subkey($guid, 'component'));
        }
        
        $_MIDCOM->set_context_item('page', $page_data);   
    }

    /**
     * Load a component and dispatch the request to it
     */
    public function dispatch($component)
    {
        // In main Midgard request we dispatch the component in connection to a page
        $page = new midgard_page();
        $page->get_by_id($_MIDGARD['page']);
        
        $component_instance = $_MIDCOM->componentloader->load($component, $page);
        $route_definitions = $component_instance->configuration->get('routes');

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
        $controller = new $controller_class($component_instance);
        
        // Then call the route_id
        $action_method = "action_{$selected_route_configuration['action']}";
        // TODO: store this array somewhere where it can be accessed via get_context_item
        $data = array();
        $controller->$action_method($this->route_id, $data, $this->action_arguments);
        $_MIDCOM->set_context_item($component, $data);
        
        // Set other context data from route
        if (isset($selected_route_configuration['mimetype']))
        {
            $_MIDCOM->set_context_item('mimetype', $selected_route_configuration['mimetype']);
        }
        if (isset($selected_route_configuration['template_entry_point']))
        {
            $_MIDCOM->set_context_item('template_entry_point', $selected_route_configuration['template_entry_point']);
        }
        if (isset($selected_route_configuration['content_entry_point']))
        {
            $_MIDCOM->set_context_item('content_entry_point', $selected_route_configuration['content_entry_point']);
        }
    }

    /**
     * Tries to match one route from an array of route definitions
     * associated with route_id route_ids
     *
     * The array should look something like this:
     * array
     * (
     *     '/view/{$article_id}/' => 'view',
     *     '/?articleid={$article_id}' => 'view',
     *     '/foo/bar' => 'someroute_id',
     *     '/latest/{$category}/{$number}' => 'categorylatest',
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
            $route_path = false;
            $route_get = false;
            $this->action_arguments = array();

            // Normalize route
            if (   strpos($route, '?') === false
                && substr($route, -1, 1) !== '/')
            {
                $route .= '/';
            }
            $route = preg_replace('%/{2,}%', '/', $route);

            //echo "DEBUG: route_id: {$route_id} route:{$route} argv_str:{$argv_str}\n";

            // Get route parts
            $route_parts = explode('?', $route, 2);
            $route_path = $route_parts[0];
            if (isset($route_parts[1]))
            {
                $route_get = $route_parts[1];
            }
            unset($route_parts);

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
                }
                // Did not match, try next route
                continue;
            }
            // "complex" route (with variable arguments)
            $route_path_regex = '%^' . str_replace('%', '\%', preg_replace('%\{\$(.+?)\}%', '(.+?)', $route_path)) . '$%';
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
                $this->action_arguments[$varname] = $route_path_regex_matches[$index+1];
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

        if (!preg_match_all('%\&?(.+?)=\{\$(.+?)\}%', $route_get, $route_get_matches))
        {
            // Can't parse arguments from route_get
            throw new Exception("GET part of route '{$route}' ('{$route_get}') cannot be parsed");
        }

        /*
        echo "DEBUG: route_get_matches\n===\n";
        print_r($route_get_matches);
        echo "===\n";
        */

        foreach ($route_get_matches[1] as $index => $get_key)
        {
            //echo "\$this->get[{$get_key}]:{$this->get[$get_key]}\n";
            if (   !isset($this->get[$get_key])
                || empty($this->get[$get_key]))
            {
                // required GET parameter not present, return false;
                $this->action_arguments = array();
                return false;
            }
            $varname = $route_get_matches[2][$index];
            $this->action_arguments[$varname] = $this->get[$get_key];
        }

        // Unlike in route_matches falling through means match
        return true;
    }
}
?>