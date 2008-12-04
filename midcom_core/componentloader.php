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
class midcom_core_componentloader
{
    private $manifests = array();
    private $tried_to_load = array();
    private $interfaces = array();

    function __construct()
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
            
        if (!isset($this->manifests[$component]))
        {
            return false;
        }
        
        if (!ereg("^[a-z][a-z0-9\_]*[a-z0-9]$", $component))
        {        
            return false;
        }
        
        return true;
    }
    
    public function load($component)
    {
        if (!$this->can_load($component))
        {
            $this->tried_to_load[$component] = false;
            return false;
        }
        
        $component_directory = $this->component_to_filepath($component);
        if (!is_dir($component_directory))
        {        
            // No component directory
            $this->tried_to_load[$component] = false;
            return false;
        }
        
        $component_interface_file = "{$component_directory}/interface.php";
        if (!file_exists($component_interface_file))
        {
            // No interface class
            // TODO: Should we default to some baseclass?
            $this->tried_to_load[$component] = false;
            return false;
        }
        require($component_interface_file);
        
        $this->interfaces[$component] = new $component();
        $this->tried_to_load[$component] = true;
        return true;
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

    private function load_manifest($manifest_file)
    {
        $manifest_yaml = file_get_contents($manifest_file);
        
        // TODO: Implement using http://spyc.sourceforge.net/ if syck is not available
        $manifest = syck_load($manifest_yaml);
        
        if (isset($this->manifests[$manifest['component']]))
        {
            return true;
        }
        
        $this->manifests[$manifest['component']] = $manifest;
        return true;
    }
    
    private function load_all_manifests()
    {
        // TODO: Cache
        exec('find ' . MIDCOM_ROOT . ' -follow -type f -name "manifest.yml"', $manifests);
        foreach ($manifests as $manifest)
        {
            $this->load_manifest($manifest);
        }
    }
}
?>