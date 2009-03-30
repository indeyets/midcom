<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Manual dispatcher for MidCOM 3
 *
 * Dispatches requested route and controller of components.
 *
 * @package midcom_core
 */
class midcom_core_services_dispatcher_manual implements midcom_core_services_dispatcher
{
    public $component_name = '';
    public $component_instance = false;
    private $page = null;
    protected $route_id = false;
    protected $action_arguments = array();

    public function __construct()
    {
    }

    /**
     * Pull data from environment into the context.
     */
    public function populate_environment_data()
    {
        $host = new midgard_host();
        $host->get_by_id($_MIDGARD['host']);
        $_MIDCOM->context->host = $host;
        $_MIDCOM->context->style_id = $_MIDCOM->context->host->style;
        $_MIDCOM->context->cache_enabled = $_MIDCOM->configuration->services_cache_configuration['enabled'];

        if (!$this->page)
        {
            $_MIDCOM->context->component = $this->component_name;
            return;
        }
           
        $_MIDCOM->context->uri = $this->get_page_prefix();        
        $_MIDCOM->context->component = $this->page->component;
        $_MIDCOM->context->page = $this->page;
        
        if ($this->page->style)
        {
            $_MIDCOM->context->style_id = $this->page->style;
        }
        
        $_MIDCOM->context->prefix = $this->get_page_prefix();
        $_MIDCOM->templating->append_page($this->page->id);
    }

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
            $_MIDCOM->timer->setMarker("MidCOM dispatcher::initialize::{$component}");
        }
        $this->component_name = $component;
        $_MIDCOM->context->component_name = $component;
        
        if ($this->page)
        {
            $_MIDCOM->context->component_instance = $_MIDCOM->componentloader->load($this->component_name, $this->page);
        }
        else
        {
            $_MIDCOM->context->component_instance = $_MIDCOM->componentloader->load($this->component_name);
        }
        
        $_MIDCOM->templating->append_directory($_MIDCOM->componentloader->component_to_filepath($this->component_name) . '/templates');
    }
    
    public function get_routes()
    {
        $core_routes = $_MIDCOM->configuration->get('routes');
        $component_routes = $_MIDCOM->context->component_instance->configuration->get('routes');
        
        return array_merge($core_routes, $component_routes);
    }
    
    public function set_page(midgard_page $page)
    {
        $this->page = $page;
    }
    
    private function get_page_prefix()
    {
        if (!$this->page)
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
        
        if ($this->page->id == $root_id)
        {
            return $prefix;
        }
        
        $page_path = '';
        $page_id = $this->page->id;
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
    
    public function set_route($route_id, array $arguments)
    {
        $this->route_id = $route_id;
        $_MIDCOM->context->route_id = $this->route_id;
        $this->action_arguments = $arguments;
    }

    /**
     * Load a component and dispatch the request to it
     */
    public function dispatch()
    {
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker("MidCOM dispatcher::dispatch::{$this->component_name}");
        }
        $route_definitions = $this->get_routes();

        $selected_route_configuration = $route_definitions[$this->route_id];

        $controller_class = $selected_route_configuration['controller'];
        $controller = new $controller_class($_MIDCOM->context->component_instance);
        
        // Then call the route_id
        $action_method = "action_{$selected_route_configuration['action']}";
        $data = array();
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker("MidCOM dispatcher::dispatch::{$this->component_name}::{$controller_class}::{$action_method}");
        }
        $controller->$action_method($this->route_id, $data, $this->action_arguments);

        if ($_MIDCOM->authentication->is_user())
        {
            // If we have user we should expose that to templating via context
            if ($this->component_name == 'midcom_core')
            {
                $data['user'] = $_MIDCOM->authentication->get_person();
            }
            else
            {
                $core_data = array
                (
                    'user' => $_MIDCOM->authentication->get_person(),
                );
                $_MIDCOM->context->set_item('midcom_core', $core_data);
            }
        }

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
        return '';
    }
}
?>