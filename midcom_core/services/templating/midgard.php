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
    private $stacks = array();
    private $stack_elements = array();

    private $elements_shown = array();

    public function __construct()
    {
        $this->stacks[0] = array();
    }
    
    public function append_directory($directory)
    {
        if (!file_exists($directory))
        {
            throw new Exception("Template directory {$directory} not found.");
        }
        $stack = $_MIDCOM->context->get_current_context();
        if (!isset($this->stacks[$stack]))
        {
            $this->stacks[$stack] = array();
        }
        $this->stacks[$stack][$directory] = 'directory';
    }
    
    public function append_style($style_id)
    {
        $stack = $_MIDCOM->context->get_current_context();
        if (!isset($this->stacks[$stack]))
        {
            $this->stacks[$stack] = array();
        }
        $this->stacks[$stack][$style_id] = 'style'; 
    }
    
    public function append_page($page_id)
    {
        $stack = $_MIDCOM->context->get_current_context();
        if (!isset($this->stacks[$stack]))
        {
            $this->stacks[$stack] = array();
        }
        $this->stacks[$stack][$page_id] = 'page';
    }
    
    private function get_element_style($style_id, $element)
    {
        $mc = midgard_element::new_collector('style', $style_id);
        $mc->add_constraint('name', '=', $element);
        $mc->set_key_property('value');
        $mc->execute();
        $keys = $mc->list_keys();
        if (count($keys) == 0)
        {
            return null;
        }
        
        foreach ($keys as $value => $array)
        {
            return $value;
        }
    }
    
    private function get_element_page($page_id, $element)
    {
        switch ($element)
        {
            case 'title':
            case 'content':
                $mc = midgard_page::new_collector('id', $page_id);
                $mc->set_key_property($element);
                $mc->execute();
                $keys = $mc->list_keys();
                if (count($keys) == 0)
                {
                    return null;
                }
                
                foreach ($keys as $value => $array)
                {
                    return $value;
                }
            default:
                $mc = midgard_pageelement::new_collector('page', $page_id);
                $mc->add_constraint('name', '=', $element);
                $mc->set_key_property('value');
                $mc->execute();
                $keys = $mc->list_keys();
                if (count($keys) == 0)
                {
                    return null;
                }
                
                foreach ($keys as $value => $array)
                {
                    return $value;
                }
        }
    }
    
    private function get_element_directory($directory, $element)
    {
        $path = "{$directory}/{$element}.php";
        if (!file_exists($path))
        {
            return null;
        }
        return file_get_contents($path);
    }
    
    private function get_element($element)
    {
        if (is_array($element))
        {
            $element = $element[1];
        }
        $stack = $_MIDCOM->context->get_current_context();
        if (!isset($this->stacks[$stack]))
        {
            throw new OutOfBoundsException("MidCOM style stack {$stack} not found.");
        }
        
        if (!isset($this->stack_elements[$stack]))
        {
            $this->stack_elements[$stack] = array();
        }
        
        if (isset($this->stack_elements[$stack][$element]))
        {
            return $this->stack_elements[$stack][$element];
        }
        
        // Reverse the stack in order to look for elements
        $reverse_stack = array_reverse($this->stacks[$stack], true);
        foreach ($reverse_stack as $identifier => $type)
        {
            $element_content = null;
            
            switch ($type)
            {
                case 'style':
                    $element_content = $this->get_element_style($identifier, $element);
                    break;
                case 'page':
                    $element_content = $this->get_element_page($identifier, $element);
                    break;
                case 'directory':
                    $element_content = $this->get_element_directory($identifier, $element);
                    break;
            }
            
            if (   $element_content
                && !in_array($element, $this->elements_shown))
            {
                $this->elements_shown[] = $element;
                
                $this->stack_elements[$stack][$element] = $element_content;
                eval('?>' . preg_replace_callback("/<\\(([a-zA-Z0-9 _-]+)\\)>/", array($this, 'get_element'), $this->stack_elements[$stack][$element]));
            }
        }
        
        // TODO: Exception or silent fail?
    }

    /**
     * Call a route of a component with given arguments and return the data it generated
     *
     * Dynamic calls may be called for either a specific page that has a component assigned to it
     * by specifying a page GUID or path as the first argument, or to a static instance of a component
     * by specifying component name as the first argument.
     *
     * @param string $component_name Component name, page GUID or page path
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
     * Dynamic loads may be called for either a specific page that has a component assigned to it
     * by specifying a page GUID or path as the first argument, or to a static instance of a component
     * by specifying component name as the first argument.
     *
     * In a TAL template dynamic load can be used in the following way:
     *
     * <code>
     * <div class="news" tal:content="structure php:MIDCOM.templating.dynamic_load('/newsfolder', 'latest', array('number' => 4))"></div>
     * </code>
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
        $this->get_element($_MIDCOM->context->template_entry_point);
    }
    
    /**
     * Include the content template based on either global or controller-specific template entry point.
     */
    public function content()
    {
        $this->get_element($_MIDCOM->context->content_entry_point);
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
                
                //TODO: Do something else here :)
                $tal->navigation = false;

                /*$tal->navigation = $_MIDCOM->navigation;
                
                if ($_MIDCOM->timer)
                {
                    $_MIDCOM->timer->setMarker('post-set-navigation');
                }*/
                
                $tal->MIDCOM = $_MIDCOM;

                if ($_MIDCOM->timer)
                {
                    $_MIDCOM->timer->setMarker('post-set-MIDCOM');
                }

                $tal->show_toolbar = false;
                if (   isset($_MIDCOM->toolbar)
                    && $_MIDCOM->toolbar->can_view())
                {
                    $tal->show_toolbar = true;
                }
                
                if ($_MIDCOM->timer)
                {
                    $_MIDCOM->timer->setMarker('post-set-show_toolbar');
                }

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
        
        if ($_MIDCOM->configuration->get('enable_included_list'))
        {
            $included = get_included_files();
            echo "<p>" . count($included) . " included files:</p>\n";
            echo "<ul>\n";
            foreach ($included as $filename)
            {
                echo "<li>{$filename}</li>\n";
            }
            echo "</ul>\n";
        }
    }
}
?>