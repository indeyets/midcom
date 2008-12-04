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
    
    private function load_page_data($page = null)
    {
        $page_data = array();
        
        if (is_null($page))
        {
            $page = $_MIDGARD['page'];
        }
        
        if (!$page)
        {
            return $page_data;
        }
        
        $mc = midgard_page::new_collector('id', $_MIDGARD['page']);
        $mc->set_key_property('guid');
        $mc->add_value_property('title');
        $mc->add_value_property('component');
        
        $mc->execute();
        $guids = $mc->list_keys();
        foreach ($guids as $guid => $array)
        {
            $page_data['title']     = $mc->get_subkey($guid, 'title');
            $page_data['component'] = $mc->get_subkey($guid, 'component');
        }
        
        return $page_data;     
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
            'page'                 => $this->load_page_data(),
            'template_engine'      => 'tal',
            'template_entry_point' => 'ROOT',
        );
        $this->current_context = $context_id;
    }
    
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

    private function set_context_item($key, $value, $context_id = null)
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
    
    public function process()
    {
        $page_data = $this->get_context_item('page');
        if (   !isset($page_data['component'])
            || !$page_data['component'])
        {        
            return;
        }

        $page = new midgard_page();
        $page->get_by_id($_MIDGARD['page']);
        
        $component_instance = $this->componentloader->load($page_data['component'], $page);
        $routes = $component_instance->configuration->get('routes');
        foreach ($routes as $route_id => $route_configuration)
        {
            // Before we have a dispatcher we just accept first route
            $controller_class = $route_configuration['controller'];
            $controller = new $controller_class($component_instance);
            
            // Then call the action
            $action_method = "action_{$route_configuration['action']}";
            $data =& $this->get_context();
            $controller->$action_method($route_id, &$data);
            
            // Set other context data from route
            if (isset($route_configuration['template_entry_point']))
            {
                $this->set_context_item('template_entry_point', $route_configuration['template_entry_point']);
            }
            
            break;
        }
    }
    
    public function display($content)
    {
        $data = $this->get_context();
        if ($data['template_engine'] == 'tal')
        {
            require('PHPTAL.php');
            $tal = new PHPTAL();
            $tal->setSource($content);
            foreach ($data as $key => $value)
            {
                $tal->$key = $value;
            }
            echo $tal->execute();
            return;
        }

        echo $content;
    }
    
    public function content()
    {
        $template_entry_point = $_MIDCOM->get_context_item('template_entry_point');
        eval('?>' . mgd_preparse(mgd_template($template_entry_point) . '<?php'));
    }
}
?>