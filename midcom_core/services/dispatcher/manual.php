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
    }

    public function initialize($component)
    {
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker("MidCOM dispatcher::initialize::{$component}");
        }
        $this->component_name = $component;
        $this->component_instance = $_MIDCOM->componentloader->load($this->component_name);
    }
    
    public function set_route($route_id, array $arguments)
    {
        $this->route_id = $route_id;
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
        $route_definitions = $this->component_instance->configuration->get('routes');

        $selected_route_configuration = $route_definitions[$this->route_id];

        $controller_class = $selected_route_configuration['controller'];
        $controller = new $controller_class($this->component_instance);
        
        // Then call the route_id
        $action_method = "action_{$selected_route_configuration['action']}";
        $data = array();
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker("MidCOM dispatcher::dispatch::{$this->component_name}::{$controller_class}::{$action_method}");
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
        return '';
    }
}
?>