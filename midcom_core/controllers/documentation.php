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
    
    private function prepare_component($component, &$data)
    {
        $data['component'] = $component;
        
        if (   $data['component'] != 'midcom_core'
            && !$_MIDCOM->componentloader->load($data['component']))
        {
            throw new midcom_exception_notfound("Component {$data['component']} not found");
        }
    }

    public function action_show($route_id, &$data, $args)
    {
        $this->prepare_component($args['variable_arguments'][0], $data);
        $path = MIDCOM_ROOT . "/{$data['component']}/documentation";
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

        if (file_exists($path))
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
        
        $data['markdown'] = file_get_contents($path);
        $data['markdown_formatted'] = Markdown($data['markdown']);
    }
    
    public function action_routes($route_id, &$data, $args)
    {
        $this->prepare_component($args['component'], $data);

        $configuration = new midcom_core_services_configuration_yaml($data['component']);
        $data['routes'] = $configuration->get('routes');
        
        if (!$data['routes'])
        {
            throw new midcom_exception_notfound("Component {$data['component']} has no routes");
        }
        
        foreach ($data['routes'] as $route_id => $route_def)
        {
            // Some normalization
            $data['routes'][$route_id]['id'] = $route_id;
            
            if (!isset($route_def['template_entry_point']))
            {
                $data['routes'][$route_id]['template_entry_point'] = 'ROOT';
            }

            if (!isset($route_def['content_entry_point']))
            {
                $data['routes'][$route_id]['content_entry_point'] = 'content';
            }
            
            $data['routes'][$route_id]['controller_action'] = "{$route_def['controller']}:{$route_def['action']}";
            
            $data['routes'][$route_id]['controller_url'] = $_MIDCOM->dispatcher->generate_url('midcom_documentation_class', array('component' => $data['component'], 'class' => $route_def['controller']));
            $data['routes'][$route_id]['controller_url'] .= "#action_{$route_def['action']}";
        }
    }
    
    public function action_class($route_id, &$data, $args)
    {
        $this->prepare_component($args['component'], $data);
        $data['class'] = $args['class'];

        if (substr($data['class'], 0, strlen($data['component'])) != $data['component'])
        {
            throw new midcom_exception_notfound("Class {$data['class']} is not in component {$data['component']}");
        }
        
        if (!class_exists($data['class']))
        {
            throw new midcom_exception_notfound("Class {$data['class']} not defined in component {$data['component']}");
        }

        $data['methods'] = array();
        $reflectionclass = new ReflectionClass($data['class']);
        $data['class_documentation'] = $reflectionclass->getDocComment();
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

            $data['methods'][] = array
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
                        " ",
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