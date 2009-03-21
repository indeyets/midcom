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

    private $gettext_translator = array();

    public function __construct()
    {
        $this->stacks[0] = array();
    }

    private function get_cache_identifier()
    {
        if (!isset($_MIDCOM->context->page))
        {
            return "{$_MIDCOM->context->host->id}-{$_MIDCOM->context->component_name}-{$_MIDGARD['style']}-" . $_MIDCOM->context->get_current_context() . 
                "-{$_MIDCOM->context->route_id}-{$_MIDCOM->context->template_entry_point}-{$_MIDCOM->context->content_entry_point}";
        }
        if (isset($_MIDCOM->context->route_id))
        {
            return "{$_MIDCOM->context->host->id}-{$_MIDCOM->context->page->id}-{$_MIDGARD['style']}-" . $_MIDCOM->context->get_current_context() . 
                "-{$_MIDCOM->context->route_id}-{$_MIDCOM->context->template_entry_point}-{$_MIDCOM->context->content_entry_point}";
        }
        return "{$_MIDCOM->context->host->id}-{$_MIDCOM->context->page->id}-{$_MIDGARD['style']}-" . $_MIDCOM->context->get_current_context() . 
            "-{$_MIDCOM->context->template_entry_point}-{$_MIDCOM->context->content_entry_point}";
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
        $this->stacks[$stack]["st:{$style_id}"] = 'style'; 
    }
    
    public function append_page($page_id)
    {
        if ($page_id != $_MIDCOM->context->page->id)
        {
            // Register page to template cache        
            $page = new midgard_page($page_id);
            $_MIDCOM->cache->template->register($this->get_cache_identifier(), array($page->guid));
        }

        $stack = $_MIDCOM->context->get_current_context();
        if (!isset($this->stacks[$stack]))
        {
            $this->stacks[$stack] = array();
        }
        $this->stacks[$stack]["pg:{$page_id}"] = 'page';
    }
    
    private function get_element_style($style_id, $element)
    {
        $mc = midgard_element::new_collector('style', $style_id);
        $mc->add_constraint('name', '=', $element);
        $mc->set_key_property('value');
        $mc->add_value_property('guid');
        $mc->execute();
        $keys = $mc->list_keys();
        if (count($keys) == 0)
        {
            return null;
        }
        
        foreach ($keys as $value => $array)
        {
            // Register element to template cache
            $_MIDCOM->cache->template->register($this->get_cache_identifier(), array($mc->get_subkey($value, 'guid')));

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
                $mc->add_value_property('guid');
                $mc->execute();
                $keys = $mc->list_keys();
                if (count($keys) == 0)
                {
                    return null;
                }
                
                foreach ($keys as $value => $array)
                {
                    // Register element to template cache
                    $_MIDCOM->cache->template->register($this->get_cache_identifier(), array($mc->get_subkey($value, 'guid')));

                    return $value;
                }
        }
    }
    
    private function get_element_directory($directory, $element)
    {
        $path = "{$directory}/{$element}.xhtml";
        if (!file_exists($path))
        {
            // Fallback, support .php files too
            $path = "{$directory}/{$element}.php";
            if (!file_exists($path))
            {
                return null;
            }
        }
        return file_get_contents($path);
    }
    
    private function get_element($element)
    {
        if (is_array($element))
        {
            // Element is array in the preg_replace_callback case (evaluating element includes)
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
        
        if ($element == 'content')
        {
            $element = $_MIDCOM->context->content_entry_point;
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
                    $element_content = $this->get_element_style((int) substr($identifier, 3), $element);
                    break;
                case 'page':
                    $element_content = $this->get_element_page((int) substr($identifier, 3), $element);
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
                
                // Replace instances of <mgd:include>elementname</mgd:include> with contents of the element
                return preg_replace_callback("%<mgd:include[^>]*>([a-zA-Z0-9_-]+)</mgd:include>%", array($this, 'get_element'), $this->stack_elements[$stack][$element]);
            }
        }
        
        //throw new OutOfBoundsException("Element {$element} not found in MidCOM style stack.");
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

        $this->template('content_entry_point');
        $this->display();

        /* 
         * Gettext is not context safe. Here we return the "original" textdomain
         * because in dynamic call the new component may change it
         */
        $_MIDCOM->context->delete();
        $_MIDCOM->i18n->set_translation_domain($_MIDCOM->context->component_name);
    }

    /**
     * Include the template based on either global or controller-specific template entry point.
     */    
    public function template($element_identifier = 'template_entry_point')
    {
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM::templating::' . $element_identifier);
        }

        if ($_MIDCOM->componentloader)
        {
            // Let injectors do their work
            $_MIDCOM->componentloader->inject_template();
            if ($_MIDCOM->timer)
            {
                $_MIDCOM->timer->setMarker('MidCOM::templating::' . $element_identifier . '::injected');
            }
        }

        // Check if we have the element in cache already
        if ($_MIDCOM->cache->template->check($this->get_cache_identifier()))
        {
            if ($_MIDCOM->timer)
            {
                $_MIDCOM->timer->setMarker('MidCOM::templating::' . $element_identifier . '::cache_checked');
            }
            return;
        }
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM::templating::' . $element_identifier . '::cache_checked');
        }

        // Register current page to cache
        if (isset($_MIDCOM->context->page))
        {
            $_MIDCOM->cache->template->register($this->get_cache_identifier(), array($_MIDCOM->context->page->guid));
        }
        else
        {
            $_MIDCOM->cache->template->register($this->get_cache_identifier(), array($_MIDCOM->context->component_name));
        }
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM::templating::' . $element_identifier . '::registered');
        }
        
        $element = $this->get_element($_MIDCOM->context->$element_identifier);
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM::templating::' . $element_identifier . '::fetched');
        }
        
        // Template cache didn't have this template, collect it
        $_MIDCOM->cache->template->put($this->get_cache_identifier(), $element);
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM::templating::' . $element_identifier . '::cached');
        }
    }
    
    /**
     * Show the loaded contents using the template engine
     *
     * @param string $content Content to display
     */
    public function display()
    {
        $data =& $_MIDCOM->context->get();
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM::templating::display::context_fetched');
        }

        $template_file = $_MIDCOM->cache->template->get($this->get_cache_identifier());
        if ($_MIDCOM->configuration->get('enable_template_php'))
        {
            // Include the file inside output buffer to get PHP executed
            ob_start();
            include($template_file);
            $content = ob_get_clean();
        }
        else
        {
            // No PHP support, just read the TAL template
            $content = file_get_contents($template_file);
        }
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker('MidCOM::templating::display::included');
        }

        switch ($data['template_engine'])
        {
            case 'tal':
                // We use the PHPTAL class
                if (!class_exists('PHPTAL'))
                {
                    require('PHPTAL.php');
                }

                // FIXME: Rethink whole tal modifiers concept 
                include_once('TAL/modifiers.php');
                if ($_MIDCOM->timer)
                {
                    $_MIDCOM->timer->setMarker('MidCOM::templating::display::tal_included');
                }
                
                $tal = new PHPTAL($this->get_cache_identifier());
                if ($_MIDCOM->timer)
                {
                    $_MIDCOM->timer->setMarker('MidCOM::templating::display::tal_started');
                }
                
                $tal->uimessages = false;
                if ($_MIDCOM->configuration->enable_uimessages)
                {
                    if (   $_MIDCOM->uimessages->has_messages()
                        && $_MIDCOM->uimessages->can_view())
                    {
                        $tal->uimessages = $_MIDCOM->uimessages->render();
                    }
                    if ($_MIDCOM->timer)
                    {
                        $_MIDCOM->timer->setMarker('MidCOM::templating::display::uimessages_shown');
                    }
                }

                $tal->MIDCOM = $_MIDCOM;
                foreach ($data as $key => $value)
                {
                    $tal->$key = $value;
                }

                $tal->setSource($content);
                if ($_MIDCOM->timer)
                {
                    $_MIDCOM->timer->setMarker('MidCOM::templating::display::source_set');
                }

                $translator =& $_MIDCOM->i18n->set_translation_domain($_MIDCOM->context->component);
                $tal->setTranslator($translator);  
                if ($_MIDCOM->timer)
                {
                    $_MIDCOM->timer->setMarker('MidCOM::templating::display::i18n');
                }
          
                $content = $tal->execute();
                unset($tal);
                if ($_MIDCOM->timer)
                {
                    $_MIDCOM->timer->setMarker('MidCOM::templating::display::tal_executed');
                }
                
                break;
            default:
                break;
        }

        if ($_MIDCOM->context->cache_enabled)
        {
            ob_start();
        }
        
        $filters = $_MIDCOM->configuration->get('output_filters');
        if ($filters)
        {
            foreach ($filters as $filter)
            {
                foreach ($filter as $component => $method)
                {
                    $instance = $_MIDCOM->componentloader->load($component);
                    if (!$instance)
                    {
                        continue;
                    }
                    $content = $instance->$method($content);
                }
            }
        }

        echo $content;
        
        if (   $_MIDCOM->context->get_current_context() == 0
            && $_MIDCOM->context->mimetype == 'text/html')
        {
            // We're in main request, and output is HTML, so it is OK to inject some HTML to it

            if ($_MIDCOM->timer)
            {
                $profiling = $_MIDCOM->timer->getProfiling();
                $total_time = $_MIDCOM->timer->timeElapsed('Start', 'Stop');
                foreach ($profiling as $marker)
                {
                    if ($marker['name'] == 'Start')
                    {
                        continue;
                    }
                    $percentage = number_format(($marker['diff'] * 100) / $total_time, 2, '.', '');
                    $_MIDCOM->log('midcom::timer', "{$percentage} percent ({$marker['diff']}s): {$marker['name']}", 'debug');
                }
                $_MIDCOM->log('MidCOM', "Execution time {$total_time}s", 'info');
            }
        
            if ($_MIDCOM->configuration->get('enable_included_list'))
            {
                $included = get_included_files();
                $_MIDCOM->log('midcom_services_templating::display', count($included) . " included files", 'info');
                foreach ($included as $filename)
                {
                    $_MIDCOM->log('midcom_services_templating::display::included', $filename, 'debug');
                }
            }
        }
        
        if ($_MIDCOM->context->cache_enabled)
        {
            // Store the contents to content cache and display them
            $_MIDCOM->cache->content->put($_MIDCOM->context->cache_request_identifier, ob_get_contents());
            ob_end_flush();
        }

        if ($_MIDCOM->configuration->enable_uimessages)
        {
            ///TODO: Connect this to some signal that tells the MidCOM execution has ended.
            $_MIDCOM->uimessages->store();
        }
    }
     
}
?>
