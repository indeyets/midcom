<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * YAML-based configuration implementation for MidCOM 3
 *
 * @package midcom_core
 */
class midcom_core_services_configuration_yaml implements midcom_core_services_configuration
{
    private $component = '';
    private $globals = array();
    private $locals = array();
    private $objects = array();
    private $merged = array();
    
    public function __construct($component, $object = null)
    {
        $this->component = $component;
        $this->load_globals();
        $this->load_locals();
        $this->merged = array_merge($this->globals, $this->locals);
        
        if ($object)
        {
            $this->load_objects($object);
            $this->merged = array_merge($this->merged, $this->objects);
        }
    }
    
    private function load_globals()
    {
        $filename = MIDCOM_ROOT . "/{$this->component}/configuration/defaults.yml";
        if (!file_exists($filename))
        {
            return;
        }
        
        $yaml = file_get_contents($filename);
        $this->globals = $this->unserialize($yaml);
    }
    
    private function load_locals()
    {
        $snippetname = "/local-configuration/{$this->component}/configuration";
        try
        {
            $snippet = new midgard_snippet();
            $snippet->get_by_path($snippetname);
        }
        catch (Exception $e)
        {
            return;
        }
        $this->locals = $this->unserialize($snippet->code);
    }
    
    private function load_objects($object)
    {
        $mc = midgard_parameter::new_collector('parentguid', $object->guid);
        $mc->add_constraint('domain', '=', $this->component);
        $mc->set_key_property('guid');
        $mc->add_value_property('name');
        $mc->add_value_property('value');
        $mc->execute();
        $guids = $mc->list_keys();
        foreach ($guids as $guid => $array)
        {
            $key = $mc->get_subkey($guid, 'name');
            if (!$this->exists($key))
            {
                continue;
            }
            
            $this->objects[$key] = $mc->get_subkey($guid, 'value');
        }
    }

    /**
     * Retrieve a configuration key
     *
     * If $key exists in the configuration data, its value is returned to the caller.
     * If the value does not exist, an exception will be raised.
     *
     * @param string $key The configuration key to query.
     * @return mixed Its value
     * @see midcom_helper_configuration::exists()
     */
    public function get($key, $subkey=false)
    {
        if (!$this->exists($key))
        {
            throw new Exception("Configuration key {$key} does not exist.");
        }
        
        if ($subkey !== false)
        {                      
            if (! isset($this->merged[$key][$subkey]))
            {
                throw new Exception("Configuration subkey {$subkey} does not exist within key {$key}.");
            }
            
            return $this->merged[$key][$subkey];
        }

        return $this->merged[$key];
    }

    /**
     * Checks for the existence of a configuration key.
     *
     * @param string $key The configuration key to check for.
     * @return boolean True, if the key is available, false otherwise.
     */
    public function exists($key)
    {
        return array_key_exists($key, $this->merged);
    }

    /**
     * Parses configuration string and returns it in configuration array format
     *
     * @param string $configuration Configuration string
     * @return array The loaded configuration array
     */
    public function unserialize($configuration)
    {
        // TODO: Implement using http://spyc.sourceforge.net/ if syck is not available
        return syck_load($configuration);
    }
    
    /**
     * Dumps configuration array and returns it as a string
     *
     * @param array $configuration Configuration array     
     * @return string Configuration in string format
     */
    public function serialize(array $configuration)
    {
        // TODO: Implement using http://spyc.sourceforge.net/ if syck is not available
        return syck_dump($configuration);
    }
}
?>