<?php
/**
 * @package midcom.admin.help
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Online help display
 *
 * @package midcom.admin.help
 */
class midcom_admin_help_help extends midcom_baseclasses_components_handler
{
    function midcom_admin_help_help()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function get_plugin_handlers()
    {
        return Array
        (
            // Handle /<help id>/<component> displaying from other component
            'display_component' => Array
            (
                'handler' => Array('midcom_admin_help_help', 'display'),
                'variable_args' => 2,
            ),
            // Handle /<help id> displaying from current component
            'display' => Array
            (
                'handler' => Array('midcom_admin_help_help', 'display'),
                'variable_args' => 1,
            ),
            // Handle / help index for current component
            'component' => Array
            (
                'handler' => Array('midcom_admin_help_help', 'component'),
            ),
        );
    }

    function _on_initialize()
    {
        // Populate the request data with references to the class members we might need
        if (array_key_exists('aegir_interface',$this->_request_data))
        {
           $this->_config =& $this->_request_data['aegir_interface']->get_handler_config('midcom.admin.help');
        }

        // doing this here as this component most probably will not be called by itself.
        $_MIDCOM->style->prepend_component_styledir('midcom.admin.help');

        $_MIDCOM->load_library('net.nehmer.markdown');
    }

    function _get_documentation_dir($component)
    {
        $component_dir = str_replace('.', '/', $component);
        return MIDCOM_ROOT . "/{$component_dir}/documentation/";
    }

    function _generate_file_path($help_id, $component, $language)
    {
        $file = $this->_get_documentation_dir($component) . "{$help_id}.{$language}.txt";
        return $file;
    }
    
    function list_files($component)
    {
        $files = array();
        
        $path = $this->_get_documentation_dir($component);
        if (!file_exists($path))
        {
            return $files;
        }
        
        $directory = dir($path);
        while (false !== ($entry = $directory->read()))
        {
            if (substr($entry, 0, 1) == '.')
            {
                // Ignore dotfiles
                continue;
            }
            
            $filename_parts = explode('.', $entry);
            if (count($filename_parts) < 3)
            {
                continue;
            }
            
            if ($filename_parts[2] != 'txt')
            {
                // Not text file, skip
                continue;
            }
            
            if (   $filename_parts[1] != $_MIDCOM->i18n->get_current_language()
                && $filename_parts[1] != $GLOBALS['midcom_config']['i18n_fallback_language'])
            {
                // Wrong language
                continue;
            }
            
            $subject = $_MIDCOM->i18n->get_string($filename_parts[0], $component);
            
            // We need to parse the file to get a title
            $file_contents = $this->get_help_contents($filename_parts[0], $component);
            if (preg_match("/\<h1\>(.*)\<\/h1\>/", $file_contents, $titles))
            {
                $subject = $titles[1];
            }
            elseif (preg_match("/\<h2\>(.*)\<\/h2\>/", $file_contents, $titles))
            {
                $subject = $titles[1];
            }
            
            $files[$filename_parts[0]] = array
            (
                'path' => "{$path}{$entry}",
                'subject' => trim($subject),
                'lang' => $filename_parts[1],
            );
        }
        $directory->close();
        
        return $files;
    }

    /**
     * Load the file from the component's documentation directory.
     */
    function _load_file($help_id, $component)
    {
        // Check that this is a real component
        if (!array_key_exists($component, $_MIDCOM->componentloader->manifests))
        {
            // Component is not loaded
            return false;
        }

        // First try loading the file in current language
        $file = $this->_generate_file_path($help_id, $component, $_MIDCOM->i18n->get_current_language());

        if (!file_exists($file))
        {
            // If that fails, use MidCOM's default fallback language
            $file = $this->_generate_file_path($help_id, $component, $GLOBALS['midcom_config']['i18n_fallback_language']);
        }

        if (!file_exists($file))
        {
            return false;
        }

        // Load the contents
        $help_contents = file_get_contents($file);

        // Replace static URLs (URLs for screenshots etc)
        $help_contents = str_replace('MIDCOM_STATIC_URL', MIDCOM_STATIC_URL, $help_contents);

        return $help_contents;
    }

    /**
     * Load a help file and markdownize it
     */
    function get_help_contents($help_id, $component)
    {
        $marker = new net_nehmer_markdown_markdown;
        $text = $this->_load_file($help_id, $component);

        if (!$text)
        {
            return false;
        }

        // Finding [callback:some_method_of_viewer]
        if (preg_match_all('/(\[callback:(.+?)\])/', $text, $regs))
        {
            foreach ($regs[1] as $i => $value)
            {
                if ($component != $_MIDCOM->get_context_data(MIDCOM_CONTEXT_COMPONENT))
                {
                    $text = str_replace($value, "\n\n__Note:__ documentation part _{$regs[2][$i]}_ from _{$component}_ is unavailable in this MidCOM context.\n\n", $text);
                }
                else
                {
                    $method_name = "help_{$regs[2][$i]}";
                    if (method_exists($this->_master, $method_name))
                    {
                        $text = str_replace($value, $this->_master->$method_name(), $text);
                    }
                }
            }
        }

        return $marker->render($text);
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_component($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        
        $component = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_COMPONENT);
        // List documentation files
        $data['help_files'] = $this->list_files($component);

        $data['request_switch_info'] = array();
        
        $_MIDCOM->skip_page_style = true;
        $data['view_title'] = sprintf($_MIDCOM->i18n->get_string('help for %s', 'midcom.admin.help'), $_MIDCOM->i18n->get_string($component, $component));
        $_MIDCOM->set_pagetitle($data['view_title']);

        // TODO: We're using "private" members here, better expose them through a method
        $handler =& $_MIDCOM->componentloader->get_interface_class($component);
        $request =& $handler->_context_data[$_MIDCOM->get_current_context()]['handler'];
        if (!isset($request->_request_switch))
        {
            // No request switch available, skip loading it
            return true;
        }
            
        foreach ($request->_request_switch as $request_handler_id => $request_data)
        {
            if (substr($request_handler_id, 0, 12) == '____ais-help')
            {
                // Skip self
                continue;
            }

            $data['request_switch_info'][$request_handler_id] = array();
            
            // Build the dynamic_loadable URI, starting from topic path
            $data['request_switch_info'][$request_handler_id]['route'] = str_replace($_MIDGARD['prefix'], '', $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX));
            // Add fixed arguments
            $data['request_switch_info'][$request_handler_id]['route'] .= implode('/', $request_data['fixed_args']);
            // Add variable_arguments
            $i = 0;
            while ($i < $request_data['variable_args'])
            {
                if (substr($data['request_switch_info'][$request_handler_id]['route'], strlen($data['request_switch_info'][$request_handler_id]['route']) - 1) != '/')
                {
                    $data['request_switch_info'][$request_handler_id]['route'] .= '/';
                }
                $data['request_switch_info'][$request_handler_id]['route'] .= '{$args[' . $i . ']}';
                $i++;
            }
            
            if (is_array($request_data['handler']))
            {
                $data['request_switch_info'][$request_handler_id]['controller'] = $request_data['handler'][0];
                
                if (is_object($data['request_switch_info'][$request_handler_id]['controller']))
                {
                    $data['request_switch_info'][$request_handler_id]['controller'] = get_class($data['request_switch_info'][$request_handler_id]['controller']);
                }
                
                $data['request_switch_info'][$request_handler_id]['action'] = $request_data['handler'][1];
            }
        }
        
        return true;
    }

    function _show_component()
    {
        midcom_show_style('midcom_admin_help_header');
        midcom_show_style('midcom_admin_help_component');
        midcom_show_style('midcom_admin_help_footer');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_display($handler_id, $args, &$data)
    {
        if ($handler_id == '____ais-help-display_component')
        {
            $component = $args[0];
            $help_id = $args[1];
        }
        else
        {
            $help_id = $args[0];
            if (array_key_exists('aegir_interface',$this->_request_data))
            {
                $component = & $this->_request_data['aegir_interface']->_module;
            }
            else
            {
                $component = $this->_master->_component;
            }
        }

        // Check that this is a real component
        if (!array_key_exists($component, $_MIDCOM->componentloader->manifests))
        {
            // Component is not loaded
            return false;
        }

        $_MIDCOM->skip_page_style = true;

        $_MIDCOM->set_pagetitle(sprintf($_MIDCOM->i18n->get_string('help for %s in %s', 'midcom.admin.help'), $help_id, $_MIDCOM->i18n->get_string($component, $component)));

        $data['html'] = $this->get_help_contents($help_id, $component);

        if (!$data['html'])
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Cannot generate help \"{$help_id}\" for in {$component}");
            // this will exit with 404
        }

        return true;
    }

    function _show_display()
    {
        midcom_show_style('midcom_admin_help_header');
        midcom_show_style('midcom_admin_help_show');
        midcom_show_style('midcom_admin_help_footer');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_edit ($handler_id, $args, &$data)
    {
        // not yet implemented
        return false;
    }

    function _show_edit()
    {
        //$this->_controller->display_form();
    }

    function _prepare_main_toolbar()
    {
        if (array_key_exists('aegir_interface', $this->_request_data))
        {
            $this->_request_data['aegir_interface']->prepare_toolbar();
            $this->_request_data['aegir_interface']->set_current_node($this->_object->guid);
            $this->_request_data['aegir_interface']->generate_location_bar();
        }
        return;
    }

}
?>