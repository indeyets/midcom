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
    protected $contoller_name = false;
    protected $controller_arguments = array();

    public function __construct()
    {
        if (isset($_GET))
        {
            $this->get = $_GET;
        }
        if (isset($_MIDGARD['argv']))
        {
            $this->argv = $_MIDGARD['argv'];
        }
    }

    /**
     * Tries to match one route from an array of route definitions
     * associated with controller names
     *
     * The array should look something like this:
     * array
     * (
     *     '/view/{$article_id}/' => 'view',
     *     '/?articleid={$article_id}' => 'view',
     *     '/foo/bar' => 'somecontroller',
     *     '/latest/{$category}/{$number}' => 'categorylatest',
     * )
     * The route parts are automatically normalized to end with trailing slash
     * if they don't contain GET arguments
     *
     * @param array $routes map of routes to controllers
     * @return boolean indicating if route could be matched or not
     */
    public function route_matches($routes)
    {
        $argv_str = '/' . implode('/', $this->argv) . '/';
        foreach ($routes as $route => $controller)
        {
            // Reset variables
            $route_path = false;
            $route_get = false;
            $this->controller_arguments = array();

            // Normalize route
            if (   strpos($route, '?') === false
                && substr($route, -1, 1) !== '/')
            {
                $route .= '/';
            }
            //echo "DEBUG: route:{$route}\n";

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
                    $this->contoller_name = $controller;
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

            // We have a complete match, setup controller arguments and return
            $this->contoller_name = $controller;
            // Map variable arguments
            foreach ($route_path_matches[1] as $index => $varname)
            {
                $this->controller_arguments[$varname] = $route_path_regex_matches[$index+1];
            }
            return true;
        }
        // No match
        return false;
    }

    /**
     * Checks GET part of route definition and places arguments as needed
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
                $this->controller_arguments = array();
                return false;
            }
            $varname = $route_get_matches[2][$index];
            $this->controller_arguments[$varname] = $this->get[$get_key];
        }

        // Unlike in route_matches falling through means match
        return true;
    }
}
?>