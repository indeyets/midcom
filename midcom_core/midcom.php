<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM core class
 *
 * @package midcom_core
 */
class midcom_core_midcom
{
    // Services that are always available
    public $configuration;
    public $componentloader;
    public $dispatcher;
    public $toolbar;

    // Helpers
    public $context;
    public $timer = null;
    
    public function __construct($dispatcher = 'midgard')
    {
        // Register autoloader so we get all MidCOM classes loaded automatically
        spl_autoload_register(array($this, 'autoload'));

        // Load the request dispatcher
        $dispatcher_implementation = "midcom_core_services_dispatcher_{$dispatcher}";
        $this->dispatcher = new $dispatcher_implementation();
        
        $this->load_base_services();
        
        // Show the world this is Midgard
        $this->head->add_meta
        (
            array
            (
                'name' => 'generator',
                'content' => "Midgard/" . mgd_version() . " MidCOM/{$this->componentloader->manifests['midcom_core']['version']} PHP/" . phpversion()
            )
        );
        
        $this->context->create();
        
        date_default_timezone_set($this->configuration->get('default_timezone'));
    }
    
    /**
     * Load all basic services needed for MidCOM usage. This includes configuration, authorization and the component loader.
     */
    public function load_base_services()
    {   
        // Load the configuration loader and load core config
        $this->configuration = new midcom_core_services_configuration_yaml('midcom_core');

        if ($this->configuration->enable_benchmark)
        {
            // Note: PEAR is not E_STRICT compatible
            error_reporting(E_ALL);
            require_once 'Benchmark/Timer.php';
            $this->timer = new Benchmark_Timer(true);
        }
        
        // Load the component loader
        $this->componentloader = new midcom_core_component_loader();
        
        // Load the context helper
        $this->context = new midcom_core_helpers_context();

        // Load the head helper
        $this->head = new midcom_core_helpers_head
        (
            $this->configuration->enable_jquery_framework,
            $this->configuration->enable_js_midcom,
            $this->configuration->js_midcom_config
        );
    }
    
    /**
     * Helper for service initialization. Usually called via getters
     *
     * @param string $service Name of service to load
     */
    private function load_service($service)
    {
        if (isset($this->$service))
        {
            return;
        }
        
        $interface_file = MIDCOM_ROOT . "/midcom_core/services/{$service}.php";
        if (!file_exists($interface_file))
        {
            throw new Exception("Service {$service} not installed");
        }
        
        $service_implementation = $this->configuration->get("services_{$service}");
        if (!$service_implementation)
        {
            throw new Exception("No implementation defined for service {$service}");
        }
        
        $this->$service = new $service_implementation();
    }
    
    /**
     * Magic getter for service loading
     */
    public function __get($key)
    {
        $this->load_service($key);
        return $this->$key;
    }
    
    /**
     * Automatically load missing class files
     *
     * @param string $class_name Name of a missing PHP class
     */
    public function autoload($class_name)
    {
        if (class_exists($class_name))
        {
            return;
        }
        
        $path = str_replace('_', '/', $class_name) . '.php';
                
        // FIXME: Do not check against component names (ie make phing build script to build correct file tree from source)
        $path = MIDCOM_ROOT . '/' . str_replace('midcom/core', 'midcom_core', $path);
        if (   isset($_MIDCOM)
            && isset($_MIDCOM->componentloader))
        {
            $components = array_keys($_MIDCOM->componentloader->manifests);
            foreach ($components as $component)
            {
                $component_path = str_replace('_', '/', $component);
                $path = str_replace($component_path, $component, $path);
            }
        }

        if (file_exists($path))
        {
            require($path);
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Process the current request, loading the page's component and dispatching the request to it
     */
    public function process()
    {    
        if ($this->timer)
        {
            $this->timer->setMarker('MidCOM::process');
        }
        
        // Load the preferred toolbar implementation
        $services_toolbars_implementation = $this->configuration->get('services_toolbars');
        $this->toolbar = new $services_toolbars_implementation($this->configuration->get('services_toolbars_configuration'));

        // Set up templating and environment        
        $_MIDCOM->templating->append_directory(MIDCOM_ROOT . '/midcom_core/templates');
        $this->dispatcher->populate_environment_data();

        // Let injectors do their work
        $this->componentloader->inject_process();

        // Load the cache service and check for content cache
        $this->load_service('cache');
        $this->dispatcher->generate_request_identifier();
        $this->cache->register_object($this->context->page);
        $this->cache->content->check($this->context->cache_request_identifier);

        // Load component
        try
        {
            $component = $this->context->get_item('component');
        }
        catch (Exception $e)
        {
            return;
        }
        
        if ($this->configuration->enable_attachment_cache)
        {
            $classname = $this->configuration->attachment_handler;
            $handler = new $classname();
            $handler->connect_to_signals();
        }
        
        if (!$component)
        {
            $component = 'midcom_core';
        }
        
        $this->dispatcher->initialize($component);
        
        try
        {
            $this->dispatcher->dispatch();
        }
        catch (midcom_exception_unauthorized $exception)
        {
            // Pass the exception to authentication handler
            $_MIDCOM->authentication->handle_exception($exception);
        }

        header('Content-Type: ' . $this->context->mimetype);

        if ($this->timer)
        {
            $this->timer->setMarker('MidCOM::process ended');
        }
    }        
}
?>