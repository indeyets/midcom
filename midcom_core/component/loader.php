<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM 3 component loader
 *
 * @package midcom_core
 */
class midcom_core_component_loader
{
    public $manifests = array();
    public $authors = array();
    private $tried_to_load = array();
    private $interfaces = array();
    private $process_injectors = array();
    private $template_injectors = array();

    public function __construct()
    {
        $this->load_all_manifests();
    }
    
    public function can_load($component)
    {
        if (isset($this->tried_to_load[$component]))
        {
            // We have already loaded (or tried and failed to load) the component
            return $this->tried_to_load[$component];
        }
            
        if (! isset($this->manifests[$component]))
        {
            return false;
        }
        
        if ($component == 'midcom_core')
        {
            return false;
        }
        
        if (preg_match('/^[a-z][a-z0-9\_]*[a-z0-9]$/', $component) !== 1)
        {        
            return false;
        }
        
        return true;
    }
    
    public function load($component, $object = null)
    {
        if (! $this->can_load($component))
        {
            $this->tried_to_load[$component] = false;
            return false;
        }
        
        if (   isset($this->interfaces[$component])
            && $this->tried_to_load[$component])
        {
            // We have already loaded the component
            return $this->interfaces[$component];
        }
        
        $component_directory = $this->component_to_filepath($component);
        if (! is_dir($component_directory))
        {        
            // No component directory
            $this->tried_to_load[$component] = false;
            
            throw new OutOfRangeException("Component {$component} directory not found.");
        }
        
        $component_interface_file = "{$component_directory}/interface.php";
        if (! file_exists($component_interface_file))
        {
            // No interface class
            // TODO: Should we default to some baseclass?
            $this->tried_to_load[$component] = false;
            
            throw new OutOfRangeException("Component {$component} interface class file not found.");
        }
        
        if (! class_exists($component))
        {
            require($component_interface_file);
        }

        // Load configuration for the component
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker("MidCOM component loaded::load::configuration");
        }
        $configuration = new midcom_core_services_configuration_yaml($component, $object);

        // Load the interface class
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->setMarker("MidCOM component loaded::load::instantiate");
        }
        $this->interfaces[$component] = new $component($configuration);
        
        if ($_MIDCOM->head->jsmidcom_enabled)
        {
            $js_component_file = "{$component_directory}/static/component.js";
            if (file_exists($js_component_file))
            {
                $_MIDCOM->head->add_jsfile(MIDCOM_STATIC_URL . "/{$component}/component.js");
            }
        }
        
        $this->tried_to_load[$component] = true;
        return $this->interfaces[$component];
    }
    
    public function load_template($component, $template, $fallback = true)
    {
        static $component_tree = array();
        $main_component = $component;
        if (!isset($component_tree[$main_component]))
        {
            // Load component's inheritance tree
            $component_tree[$main_component] = $component;
            while (true)
            {
                $component = $this->get_parent($component);
                if ($component === null)
                {
                    break;
                }
                
                $component_tree[$main_component] = $component;
            }
            $component_tree[$main_component] = 'midcom_core';
            $component_tree[$main_component] = array_reverse($component_tree[$main_component]);
        }
        
        foreach ($component_tree[$main_component] as $component)
        {
            $component_directory = $this->component_to_filepath($component);
            $template_file = "{$component_directory}/templates/{$template}.php";
            if (!file_exists($template_file))
            {
                if (!$fallback)
                {
                    // TODO: Should we just ignore this silently instead?
                    throw new OutOfRangeException("Component {$main_component} template file {$template} not found.");
                }
                // Go to next one in tree
                continue;
            }
            
            return file_get_contents($template_file);
        }

        // TODO: Should we just ignore this silently instead?
        throw new OutOfRangeException("{$main_component} or {$component} template file {$template} not found.");
    }

    /**
     * Get the component that is parent of current component
     */
    public function get_parent($component)
    {
        return $this->manifests[$component]['extends'];
    }

    /**
     * Helper, converting a component name (net_nehmer_blog)
     * to a file path (/net/nehmer/blog).
     *
     * @param string $component Component name
     * @return string File path
     */
    public function component_to_filepath($component)
    {
        return MIDCOM_ROOT . '/' . $component;// . strtr($component, '_', '/');
    }

    /**
     * Load a component manifest file
     *
     * @param string $manifest_file Path of the manifest file
     */
    private function load_manifest($manifest_file)
    {
        if (! file_exists($manifest_file))
        {
            return false;
        }
        
        $manifest_yaml = file_get_contents($manifest_file);
        
        // TODO: Implement using http://spyc.sourceforge.net/ if syck is not available
        $manifest = syck_load($manifest_yaml);
        
        // Normalize manifest
        if (!isset($manifest['version']))
        {
            $manifest['version'] = '0.0.1devel';
        }
        if (!isset($manifest['authors']))
        {
            $manifest['authors'] = array();
        }
        if (!isset($manifest['extends']))
        {
            $manifest['extends'] = null;
        }
        foreach ($manifest['authors'] as $username => $author)
        {
            if (!isset($author['name']))
            {
                $manifest['authors'][$username]['name'] = '';
            }
            
            if (!isset($author['url']))
            {
                $manifest['authors'][$username]['url'] = 'http://www.midgard-project.org';
            }
            
            if (!isset($this->authors[$username]))
            {
                $this->authors[$username] = $manifest['authors'][$username];
            }
        }
        
        if (!isset($this->manifests[$manifest['component']]))
        {
            $this->manifests[$manifest['component']] = $manifest;
        }
        
        if (isset($manifest['process_injector']))
        {
            // This component has an injector for the process() phase
            $this->process_injectors[$manifest['component']] = $manifest['process_injector'];
        }

        if (isset($manifest['template_injector']))
        {
            // This component has an injector for the template() phase
            $this->template_injectors[$manifest['component']] = $manifest['template_injector'];
        }
    }

    private function load_all_manifests_uncached()
    {
        // Uncached manifest loading is very slow
        exec('find ' . escapeshellarg(MIDCOM_ROOT) . ' -follow -type f -name ' . escapeshellarg('manifest.yml'), $manifests);
        foreach ($manifests as $manifest)
        {
            if (strpos($manifest, 'scaffold') === false)
            {
                $this->load_manifest($manifest);                
            }
        }
    }

    private function load_all_manifests()
    {
        if (!extension_loaded('memcache'))
        {
            $this->load_all_manifests_uncached();
            return;
        }

        // TODO: Refactor to utilize cache service infrastructure
        $memcache = new Memcache();
        if (!@$memcache->connect('localhost'))
        {
            // Couldn't connect
            $this->load_all_manifests_uncached();
            return;
        }
        
        $prefix = "{$_MIDGARD['sitegroup']}-{$_MIDGARD['host']}"; // FIXME: Take account midgard configuration as it's possible

        if (!$manifests = $memcache->get($prefix . 'manifests'))
        {
            exec('find ' . escapeshellarg(MIDCOM_ROOT) . ' -follow -type f -name ' . escapeshellarg('manifest.yml'), $manifests);
            $memcache->set($prefix . 'manifests', $manifests, false, 600);
        }
        foreach ($manifests as $manifest)
        {
            if (strpos($manifest, 'scaffold') === false)
            {
                $this->load_manifest($manifest);                
            }
        }
        $memcache->close();
    }

    /**
     * Injectors are component classes that manipulate the context
     */
    private function inject($injector_type)
    {
        $injector_array = "{$injector_type}_injectors";
        $injector_method = "inject_{$injector_type}";
        foreach ($this->$injector_array as $component => $injector_class)
        {
            // Ensure the component is loaded
            $this->load($component);

            // Instantiate the injector class
            $injector = new $injector_class();
            
            // Inject
            $injector->$injector_method();
        }
    }

    public function inject_process()
    {
        $this->inject('process');
    }

    public function inject_template()
    {
        $this->inject('template');
    }
}
?>
