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
    private $merged = array();
    
    public function __construct($component)
    {
        $this->component = $component;
        $this->load_globals();
        $this->load_locals();
        
        $this->merged = array_merge($this->globals, $this->locals);
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
    public function get($key)
    {
        if (!$this->exists($key))
        {
            throw new Exception("Configuration key {$key} does not exist.");
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
        return isset($this->merged[$key]);
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