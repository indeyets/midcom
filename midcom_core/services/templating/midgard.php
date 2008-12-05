<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Midgard-based templating interface for MidCOM 3
 *
 * @package midcom_core
 */
class midcom_core_services_templating_midgard implements midcom_core_services_templating
{
    private $dispatcher = null;

    /**
     * Call a route of a component with given arguments and return the data it generated
     *
     * @param string $component_name Component name or page GUID
     * @param string $route_id     Route identifier
     * @param array $arguments  Arguments to give to the route
     * @param boolean $switch_context Whether to run the route in a new context
     * @return $array data
     */
    public function dynamic_call($component_name, $route_id, array $arguments, $switch_context = true)
    {
        if (is_null($this->dispatcher))
        {
            $this->dispatcher = new midcom_core_services_dispatcher_manual();
        }
        
        if ($switch_context)
        {
            $_MIDCOM->context->create();
        }
        
        $page = null;

        if (mgd_is_guid($component_name))
        {
            $page = new midgard_page($component_name);
        }
        elseif (strpos($component_name, '/') !== false)
        {
        mgd_debug_start();
            $page = new midgard_page();
            $page->get_by_path($component_name);
        }
        
        if ($page)
        {
            $component_name = $page->component;
            
            if (!$component_name)
            {
                throw new Exception("Page {$page->guid} has no component defined");
            }
            
            $this->dispatcher->set_page($page);
        }

        $this->dispatcher->populate_environment_data();
        $this->dispatcher->initialize($component_name);
        
        if (!$_MIDCOM->context->component_instance->configuration->exists('routes'))
        {
            throw new Exception("Component {$component_name} has no routes defined");
        }
        
        $routes = $_MIDCOM->context->component_instance->configuration->get('routes');
        if (!isset($routes[$route_id]))
        {
            throw new Exception("Component {$component_name} has no route {$route_id}");
        }

        $this->dispatcher->set_route($route_id, $arguments);
        $this->dispatcher->dispatch();
        
        $data = $_MIDCOM->context->$component_name;

        if ($switch_context)
        {        
            $_MIDCOM->context->delete();
        }
        
        return $data;
    }
    
    /**
     * Call a route of a component with given arguments and display its content entry point
     *
     * @param string $component_name Component name or page GUID
     * @param string $route_id     Route identifier
     * @param array $arguments  Arguments to give to the route
     * @return $array data
     */
    public function dynamic_load($component_name, $route_id, array $arguments)
    {
        $_MIDCOM->context->create();
        $data = $this->dynamic_call($component_name, $route_id, $arguments, false);
        
        ob_start();
        $this->content();

        $this->display(ob_get_clean());
        
        $_MIDCOM->context->delete();
    }

    /**
     * Include the template based on either global or controller-specific template entry point.
     */    
    public function template()
    {
        $template_entry_point = $_MIDCOM->context->get_item('template_entry_point');

        $component = $_MIDCOM->context->get_item('component');
        if (   !mgd_is_element_loaded($template_entry_point)
            && $component)
        {        
            // Load element from component templates
            echo $_MIDCOM->componentloader->load_template($component, $template_entry_point);
        }
        else
        {
            eval('?>' . mgd_preparse(mgd_template($template_entry_point)));
        }
    }
    
    /**
     * Include the content template based on either global or controller-specific template entry point.
     */
    public function content()
    {
        $content_entry_point = $_MIDCOM->context->get_item('content_entry_point');
        
        $page_data = $_MIDCOM->context->get_item('page');

        $component = $_MIDCOM->context->get_item('component');

        if (   !mgd_is_element_loaded($content_entry_point)
            && $component)
        {        
            // Load element from component templates
            echo $_MIDCOM->componentloader->load_template($component, $content_entry_point);
        }
        else
        {
            eval('?>' . mgd_preparse(mgd_template($content_entry_point)));
        }
    }
    
    /**
     * Show the loaded contents using the template engine
     *
     * @param string $content Content to display
     */
    public function display($content)
    {
        $data = $_MIDCOM->context->get();
        switch ($data['template_engine'])
        {
            case 'tal':
                if (!class_exists('PHPTAL'))
                {
                    require('PHPTAL.php');
                    include('TAL/modifiers.php');
                }
                
                if ($_MIDCOM->timer)
                {
                    $_MIDCOM->timer->setMarker('post-require');
                }
                
                $tal = new PHPTAL();
                $tal->setSource($content);

                if ($_MIDCOM->timer)
                {
                    $_MIDCOM->timer->setMarker('post-source');
                }
                
                $tal->navigation = $_MIDCOM->navigation;
                
                if ($_MIDCOM->timer)
                {
                    $_MIDCOM->timer->setMarker('post-set-navigation');
                }
                
                $tal->MIDCOM = $_MIDCOM;

                foreach ($data as $key => $value)
                {
                    $tal->$key = $value;
                    
                    if ($_MIDCOM->timer)
                    {
                        $_MIDCOM->timer->setMarker("post-set-{$key}");
                    }
                }
                
                $content = $tal->execute();
                
                if ($_MIDCOM->timer)
                {
                    $_MIDCOM->timer->setMarker('post-execute');
                }
                break;
            default:
                break;
        }

        echo $content;
        
        if (   $_MIDCOM->timer
            && $_MIDCOM->context->get_current_context() == 0)
        {
            $_MIDCOM->timer->display();
        }
    }
}
?>