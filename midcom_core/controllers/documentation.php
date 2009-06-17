<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM documentation display controller
 *
 * @package midcom_core
 */
class midcom_core_controllers_documentation
{
    public function __construct($instance)
    {
        $this->configuration = $_MIDCOM->configuration;
    }
    
    private function prepare_component($component)
    {
        $this->data['component'] = $component;
        
        if (   $this->data['component'] != 'midcom_core'
            && !$_MIDCOM->componentloader->load($this->data['component']))
        {
            throw new midcom_exception_notfound("Component {$this->data['component']} not found");
        }
    }

    private function list_directory($path, $prefix = '')
    {
        $files = array
        (
            'name'    => basename($path),
            'label'   => ucfirst(str_replace('_', ' ', basename($path))),
            'folders' => array(),
            'files'   => array(),
        );

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

            if (is_dir("{$path}/{$entry}"))
            {
                // List subdirectory
                $files['folders'][$entry] = $this->list_directory("{$path}/{$entry}", "{$prefix}{$entry}/");
                continue;
            }
            
            $pathinfo = pathinfo("{$path}/{$entry}");
            
            if (   !isset($pathinfo['extension'])
                || $pathinfo['extension'] != 'markdown')
            {
                // We're only interested in Markdown files
                continue;
            }
            
            $files['files'][] = array
            (
                'label' => ucfirst(str_replace('_', ' ', $pathinfo['filename'])),
                'path' => "{$prefix}{$pathinfo['filename']}/",
            );
        }
        $directory->close();
        return $files;
    }

    public function get_index($args)
    {
        $_MIDCOM->authorization->require_user();
        $this->prepare_component($args['component'], $this->data);

        $this->data['files'] = $this->list_directory(MIDCOM_ROOT . "/{$this->data['component']}/documentation");

        $configuration = new midcom_core_services_configuration_yaml($this->data['component']);
        $this->data['routes'] = $configuration->get('routes');
        if ($this->data['routes'])
        {
            $this->data['files']['files'][] = array
            (
                'label' => 'Routes',
                'path' => 'routes/',
            );
        }
    }

    public function get_show($args)
    {
        $_MIDCOM->authorization->require_user();
        $this->prepare_component($args['variable_arguments'][0], $this->data);
        $path = MIDCOM_ROOT . "/{$this->data['component']}/documentation";
        foreach ($args['variable_arguments'] as $key => $argument)
        {
            if ($key == 0)
            {
                continue;
            }
            
            if ($argument == '..')
            {
                continue;
            }
            
            $path .= "/{$argument}";
        }

        if (   file_exists($path)
            && !is_dir($path))
        {
            // Image or other non-Markdown doc file, pass directly
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $mimetype = 'application/octet-stream';
            switch ($extension)
            {
                case 'png':
                    $mimetype = 'image/png';
                    break;
            }
            header("Content-type: {$mimetype}");
            readfile($path);
            die();
        }

        require_once 'markdown.php';

        $path .= '.markdown';
        if (!file_exists($path))
        {
            throw new midcom_exception_notfound("File not found");
        }
        
        $this->data['markdown'] = file_get_contents($path);
        $this->data['markdown_formatted'] = Markdown($this->data['markdown']);
    }
    
    public function get_routes($args)
    {
        $_MIDCOM->authorization->require_user();
        $this->prepare_component($args['component'], $this->data);

        $configuration = new midcom_core_services_configuration_yaml($this->data['component']);
        $this->data['routes'] = $configuration->get('routes');
        
        if (!$this->data['routes'])
        {
            throw new midcom_exception_notfound("Component {$this->data['component']} has no routes");
        }
        
        foreach ($this->data['routes'] as $route_id => $route_def)
        {
            // Some normalization
            $this->data['routes'][$route_id]['id'] = $route_id;
            
            if (!isset($route_def['template_entry_point']))
            {
                $this->data['routes'][$route_id]['template_entry_point'] = 'ROOT';
            }

            if (!isset($route_def['content_entry_point']))
            {
                $this->data['routes'][$route_id]['content_entry_point'] = 'content';
            }
            
            $this->data['routes'][$route_id]['controller_action'] = "{$route_def['controller']}:{$route_def['action']}";
            
            $this->data['routes'][$route_id]['controller_url'] = $_MIDCOM->dispatcher->generate_url('midcom_documentation_class', array('component' => $this->data['component'], 'class' => $route_def['controller']));
            $this->data['routes'][$route_id]['controller_url'] .= "#action_{$route_def['action']}";
        }
    }
    
    public function get_class($args)
    {
        $_MIDCOM->authorization->require_user();
        $this->prepare_component($args['component'], $this->data);
        $this->data['class'] = $args['class'];

        if (substr($this->data['class'], 0, strlen($this->data['component'])) != $this->data['component'])
        {
            throw new midcom_exception_notfound("Class {$this->data['class']} is not in component {$this->data['component']}");
        }
        
        if (!class_exists($this->data['class']))
        {
            throw new midcom_exception_notfound("Class {$this->data['class']} not defined in component {$this->data['component']}");
        }

        $this->data['methods'] = array();
        $reflectionclass = new ReflectionClass($this->data['class']);
        $this->data['class_documentation'] = midcom_core_controllers_documentation::render_docblock($reflectionclass->getDocComment());
        $reflectionmethods = $reflectionclass->getMethods();
        foreach ($reflectionmethods as $method)
        {
            $arguments = '';
            $parametersdata = array();
            $parameters = $method->getParameters();
            foreach ($parameters as $reflectionparameter)
            {
                $parametersignature = '';
                
                if ($reflectionparameter->isPassedByReference())
                {
                    $parametersignature .= '&';
                }
                   
                $parametersignature .= '$' . str_replace(' ', '_', $reflectionparameter->getName());
                
                if ($reflectionparameter->isDefaultValueAvailable())
                {
                    $parametersignature .= ' = ' . $reflectionparameter->getDefaultValue();
                }

                if ($reflectionparameter->isOptional())
                {
                    $parametersignature = "[{$parametersignature}]";
                }

                $parametersdata[] = $parametersignature;
            }
            $arguments .= '(' . implode(', ', $parametersdata) . ')';
            $modifiers = implode(' ' , Reflection::getModifierNames($method->getModifiers()));

            $this->data['methods'][] = array
            (
                'name' => $method->name,
                'modifiers' => $modifiers,
                'arguments' => $arguments,
                'signature' => "{$modifiers} {$method->name}{$arguments}",
                'documentation' => midcom_core_controllers_documentation::render_docblock($method->getDocComment()),
            );
        }
    }

    /**
     * Simple way to render PHPDoc-blocks to HTML
     *
     * @param string $docblock the PHPDoc definition as written in the code
     * @return string HTML presentation
     */
    static public function render_docblock($docblock)
    {
        if (empty($docblock))
        {
            return $docblock;
        }
        // Just to be sure normalize newlines
        $docblock = preg_replace("/\n\r|\r\n|\r/","\n", $docblock);
        // Strip start and end of comment
        $tmp1 = preg_replace('%/\*\*\s*\n(.*?)\s*\*/%ms', '\\1', $docblock);
        // Strip *s from start of line
        $tmp1 = preg_replace('%^\s*\*\s?%m', '', $tmp1);
        // convert lines of only whitespace to simple newlines
        /**
         * did not work
        $tmp1 = preg_replace('%\s+\n%m', "\n", $tmp1);
         */
        // Entitize significant whitespace
        $ws_matches =  array();
        if (preg_match('%^ {2,}|\t+%m', $tmp1, $ws_matches))
        {
            foreach ($ws_matches as $ws_string)
            {
                $replace = str_replace
                (
                    array
                    (
                        ' ',
                        "\t",
                    ),
                    array
                    (
                        '&nbsp;',
                        "&nbsp;&nbsp;&nbsp;&nbsp;",
                    ),
                    $ws_string
                );
                $tmp1 = str_replace($ws_string, $replace, $tmp1);
            }
        }
        // Separate first line and rest of it
        $parts = explode("\n", $tmp1, 2);
        if (count($parts) === 2)
        {
            $summary = $parts[0];
            $comment = $parts[1];
            $ret = "<div class='summary'>{$summary}</div>\n<div class='comments'>" . nl2br($comment) . "</div>\n";
        }
        else
        {
            $summary = $parts[0];
            $ret = "<div class='summary'>{$summary}</div>\n";
        }
        /*
        echo "DEBUG: ret<pre>\n";
        echo htmlentities($ret);
        echo "</pre>\n";
        */
        return $ret;
    }
}
?>