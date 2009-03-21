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
    
    public function action_routes($route_id, &$data, $args)
    {
        $data['component'] = $args['component'];
        
        if (   $data['component'] != 'midcom_core'
            && !$_MIDCOM->componentloader->load($data['component']))
        {
            throw new midcom_exception_notfound("Component {$data['component']} not found");
        }
    
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
        $data['component'] = $args['component'];
        $data['class'] = $args['class'];
        
        if (   $data['component'] != 'midcom_core'
            && !$_MIDCOM->componentloader->load($data['component']))
        {
            throw new midcom_exception_notfound("Component {$data['component']} not found");
        }

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
                'documentation' => $method->getDocComment(),
            );
        }
    }
}
?>