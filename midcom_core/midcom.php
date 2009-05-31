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
        
        $this->context->create();
        
        date_default_timezone_set($this->configuration->get('default_timezone'));
        
        midgard_connection::set_loglevel($this->configuration->get('log_level'));

        if ($this->timer)
        {
            $this->timer->setMarker('MidCOM::initialized');
        }
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

        // Load the context helper
        $this->context = new midcom_core_helpers_context();

        // Load the head helper
        $this->head = new midcom_core_helpers_head($this->configuration);
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
     * Logging interface
     *
     * @param string $prefix Prefix to file the log under
     * @param string $message Message to be logged
     * @param string $loglevel Logging level, may be one of debug, info, message and warning
     */
    public function log($prefix, $message, $loglevel = 'debug')
    {
        if (!extension_loaded('midgard2'))
        {
            // Temporary non-Midgard logger until midgard_error is backported to Ragnaroek
            static $logger = null;
            if (!$logger)
            {
                $logger = new midcom_core_helpers_log();
            }
            static $log_levels = array
            (
                'debug' => 4,
                'info' => 3,
                'message' => 2,
                'warn' => 1,
            );
            
            if ($log_levels[$loglevel] > $log_levels[$this->configuration->get('log_level')])
            {
                // Skip logging, too low level
                return;
            }
            $logger->log("{$prefix}: {$message}");
            return;
        }
        
        $loglevel = str_replace('warn', 'warning', $loglevel);
        
        midgard_error::$loglevel("{$prefix}: {$message}");
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
        $this->dispatcher->populate_environment_data();
        if ($this->timer)
        {
            $this->timer->setMarker('MidCOM::process::env_populated');
        }
        $this->log('MidCOM', "Serving {$this->dispatcher->request_method} {$_MIDCOM->context->uri} at " . gmdate('r'), 'info');

        // Let injectors do their work
        $this->componentloader = new midcom_core_component_loader();
        $this->componentloader->inject_process();
        if ($this->timer)
        {
            $this->timer->setMarker('MidCOM::process::injected');
        }

        // Load the cache service and check for content cache
        $this->load_service('cache');
        if ($_MIDCOM->context->cache_enabled)
        {
            $this->dispatcher->generate_request_identifier();
            $this->cache->register_object($this->context->page);
            $this->cache->content->check($this->context->cache_request_identifier);
            if ($this->timer)
            {
                $this->timer->setMarker('MidCOM::process::cache_checked');
            }
        }

        // Show the world this is Midgard
        $this->head->add_meta
        (
            array
            (
                'name' => 'generator',
                'content' => "Midgard/" . mgd_version() . " MidCOM/{$this->componentloader->manifests['midcom_core']['version']} PHP/" . phpversion()
            )
        );

        // Load component
        try
        {
            $component = $this->context->get_item('component');
        }
        catch (Exception $e)
        {
            return;
        }
        if (!$component)
        {
            $component = 'midcom_core';
        }

        if ($this->configuration->enable_attachment_cache)
        {
            $classname = $this->configuration->attachment_handler;
            $handler = new $classname();
            $handler->connect_to_signals();
            if ($this->timer)
            {
                $this->timer->setMarker('MidCOM::process::attachment_cache_enabled');
            }
        }

        // Set up templating stack: midcom_core goes first 
        $_MIDCOM->templating->append_directory(MIDCOM_ROOT . '/midcom_core/templates');

        // Then initialize the component, so it also goes to template stack
        $this->dispatcher->initialize($component);
        if ($this->timer)
        {
            $this->timer->setMarker('MidCOM::process::dispatcher_initialized');
        }

        // And finally append style and page to template stack
        $_MIDCOM->templating->append_style($this->context->style_id);
        $_MIDCOM->templating->append_page($this->context->page->id);
        if ($this->timer)
        {
            $this->timer->setMarker('MidCOM::process::template_stack_prepared');
        }

        try
        {
            $this->dispatcher->dispatch();
        }
        catch (midcom_exception_unauthorized $exception)
        {
            // Pass the exception to authentication handler
            $_MIDCOM->authentication->handle_exception($exception);
        }
        if ($this->timer)
        {
            $this->timer->setMarker('MidCOM::process::dispatched');
        }

        header('Content-Type: ' . $this->context->mimetype);
    }
    
    public function serve()
    {
        // Handle HTTP request
        if ($_MIDCOM->context->webdav_request)
        {
            // Start the full WebDAV server instance
            // FIXME: Figure out how to prevent this with Variants
            $webdav_server = new midcom_core_helpers_webdav();
            $webdav_server->serve();
            // This will exit
        }
        if ($this->timer)
        {
            $this->timer->setMarker('MidCOM dispatcher::dispatch_route::webdav_checked');
        }

        // Prepate the templates
        $this->templating->template();

        // Read contents from the output buffer and pass to MidCOM rendering
        $this->templating->display();
    }
}
?>
