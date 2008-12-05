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
    public $authorization;
    public $configuration;
    public $componentloader;
    public $dispatcher;
    
    private $contexts = array();
    private $current_context = 0;

    public function __construct($dispatcher = 'midgard')
    {
        // Register autoloader so we get all MidCOM classes loaded automatically
        spl_autoload_register(array($this, 'autoload'));

        // Load the request dispatcher
        $dispatcher_implementation = "midcom_core_services_dispatcher_{$dispatcher}";
        $this->dispatcher = new $dispatcher_implementation();
        
        $this->load_base_services();
        $this->create_context();
    }
    
    /**
     * Load all basic services needed for MidCOM usage. This includes configuration, authorization and the component loader.
     */
    public function load_base_services()
    {   
        // Load the configuration loader and load core config
        $this->configuration = new midcom_core_services_configuration_yaml('midcom_core');
        
        // Load the preferred authorization implementation
        $services_authorization_implementation = $this->configuration->get('services_authorization');
        $this->authorization = new $services_authorization_implementation();
        
        // Load the component loader
        $this->componentloader = new midcom_core_componentloader();
    }
    
    
    /**
     * Create and prepare a new component context.
     *
     * @return int The ID of the newly created component.
     * @access private
     */
    private function create_context()
    {
        $context_id = count($this->contexts);
        $this->contexts[$context_id] = array
        (
            'mimetype'             => 'text/html',
            'template_engine'      => 'tal',
            'template_entry_point' => 'ROOT',
            'content_entry_point'  => 'content',
        );
        $this->current_context = $context_id;
    }
    
    /**
     * Get a reference of the context data array
     *
     * @param int $context_id ID of the current context
     * @return array Context data
     */
    public function &get_context($context_id = null)
    {
        if (is_null($context_id))
        {
            $context_id = $this->current_context;
        }
        
        if (!isset($this->contexts[$context_id]))
        {
            throw new Exception("MidCOM context {$context_id} not found.");
        }
        
        return $this->contexts[$context_id];
    }

    /**
     * Get value of a particular context data array item
     *
     * @param string $key Key to get data of
     * @param int $context_id ID of the current context
     * @return array Context data
     */
    public function get_context_item($key, $context_id = null)
    {
        if (is_null($context_id))
        {
            $context_id = $this->current_context;
        }
        
        if (!isset($this->contexts[$context_id]))
        {
            throw new Exception("MidCOM context {$context_id} not found.");
        }
        
        if (!isset($this->contexts[$context_id][$key]))
        {
            throw new Exception("MidCOM context key '{$key}' in context {$context_id} not found.");
        }
        
        return $this->contexts[$context_id][$key];
    }

    /**
     * Set value of a particular context data array item
     *
     * @param string $key Key to set data of
     * @param mixed $value Value to set to the context data array
     * @param int $context_id ID of the current context
     * @return array Context data
     */
    public function set_context_item($key, $value, $context_id = null)
    {
        if (is_null($context_id))
        {
            $context_id = $this->current_context;
        }
        
        if (!isset($this->contexts[$context_id]))
        {
            throw new Exception("MidCOM context {$context_id} not found.");
        }
        
        $this->contexts[$context_id][$key] = $value;
        return true;
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
        
        // TODO: Check against component names
        $path = MIDCOM_ROOT . '/' . str_replace('midcom/core', 'midcom_core', $path);
        $path = str_replace('net/nemein/news', 'net_nemein_news', $path);

        if (!file_exists($path))
        {
            throw new Exception("File {$path} not found, aborting.");
        }
        
        require($path);
    }
    
    /**
     * Process the current request, loading the page's component and dispatching the request to it
     */
    public function process()
    {
        $this->dispatcher->populate_environment_data();
        try
        {
            $component = $this->get_context_item('component');
        }
        catch (Exception $e)
        {
            return;
        }

        $this->dispatcher->dispatch($component);
        
        //header('Content-Type: ' . $this->get_context_item('mimetype'));
    }

    /**
     * Include the template based on either global or controller-specific template entry point.
     */    
    public function template()
    {
        $template_entry_point = $_MIDCOM->get_context_item('template_entry_point');

        $component = $this->get_context_item('component');
        if (   !mgd_is_element_loaded($template_entry_point)
            && $component)
        {        
            // Load element from component templates
            echo $this->componentloader->load_template($component, $template_entry_point);
        }
        else
        {
            eval('?>' . mgd_preparse(mgd_template($template_entry_point)));
        }
    }
    
    /**
     * Include the content template based on either global or controller-specific template entry point.
     */
    public function content()
    {
        $content_entry_point = $_MIDCOM->get_context_item('content_entry_point');

        $component = $this->get_context_item('component');
        if (   !mgd_is_element_loaded($content_entry_point)
            && $component)
        {        
            // Load element from component templates
            echo $this->componentloader->load_template($component, $content_entry_point);
        }
        else
        {
            eval('?>' . mgd_preparse(mgd_template($content_entry_point)));
        }
    }
    
    /**
     * Show the loaded contents using the template engine
     *
     * @param string $content Content to display
     */
    public function display($content)
    {
        $data = $this->get_context();
        switch ($data['template_engine'])
        {
            case 'tal':
                require('PHPTAL.php');
                $tal = new PHPTAL();
                $tal->setSource($content);
                foreach ($data as $key => $value)
                {
                    $tal->$key = $value;
                }
                $content = $tal->execute();
                break;
            default:
                break;
        }

        echo $content;
    }

}
?>