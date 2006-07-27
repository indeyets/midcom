<?php
/**
 * Created on Sep 3, 2005
 * @author tarjei huse
 * @package midcom.admin.aegir 
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 * This is the basic Aegir module interface. 
 * It provides some datastructures Aegir and it's handlers need.'.

 */
 
class midcom_admin_aegir_module extends midcom_baseclasses_components_purecode {

    /**
     * The name of the module 
     *
     * @var string
     */
    var $_module = ''; 

    /**
     * Base class name.
     *
     */
    var $_class = '';
    
    /**
     * Navigation class
     * @access private
     * @abstract Change the value of this variable if you want to name the 
     * aegir_module_navigation class something else. 
     */
    var $_navigation_class = null;
    
    /**
     * pointer to navigation module
     * @var midcom_admin_aegir_module_navigation subclass
     * @access private (use get_nav())
     */
    var $_nav = null;
    /**
     * Current module key in the module registry
     * @access public
     * @var string
     */
     var $current = '';
    
    /**
     * Pointer to the local module configuration
     * @var midcom_helper_configuration
     * @access public
     */
     var $module_config = null;
     
    /**
     * Pointer to the configuratior for the current handler.
     */
    
    /**
     * Module registry
     * @var array
     * @access public
     */
    var $registry = array();
    /**
     * This is used during initialization when loading the default configurations
     * from the filesystem ($prefix/config/$name.inc) and the snippetdirs
     * ($GLOBALS['midcom_config']['midcom_sgconfig_basedir']/$component/$name). 
     * They will be merged and placed into the
     * component data store under a key with the same name then the snippet as
     * a midcom_helper_configuration object.
     *
     * Set this to null to disable automatic configuration handling.
     *
     * @var string
     */
    var $_config_snippet_name = 'config';

    /**
     * The full path to the components' root directory. Used for loading files.
     *
     * @var string
     */
    var $_component_path = '';
    
    /**
     * A list of files, relative to the components root directory, that
     * should be loaded during initialization.
     *
     * @var Array
     */
    var $_autoload_files = Array();

    /**
     * A list of libraries which should by loaded during initialization.
     * This will be done before actually loading the script files from
     * _autoload_files.
     *
     * @var Array
     */
    var $_autoload_libraries = Array();
    
    
    /**
     * The current leaf that is beeing handled
     * 
     * NOTE: It is the responsibility of the current handler to set this value!
     * 
     * @var string leaf id
     * @access public
     * */
    var $current_leaf = null;
    
    /**
     * the current node beeing worked on
     * NOTE: It is the responsibility of the current handler to set this value!
     * @var string node id
     * @access public
     */
     var $current_node = null;
    
    /**
     * Argv array
     * Mainly for the benefit of handlers wanting to set things across different handling
     * functions without havng to do it all the time.
     * @var array
     * @access protected
     */
     var $_argv = null;
     /**
      * Argc array
      * Size of the argv array;
      * @var int
      * @access protected
      */
    var $_argc = 0;
    
    /**
     * Maximum level for the menu to decend and expand nodes. 
     * @var int number of levels
     */
    var $nav_maxlevel = 1;
   
    /**
     * Constructur function. 
     */
    function midcom_admin_aegir_interface () 
    {
        
        
        
    }
      
    /* 
     * loads the files needed, initiates various variables.
     * */
    function _initialize () 
    {
        $this->_module = $this->registry[$this->current]['component'];
        $this->_class = str_replace('.','_', $this->_module);
        $loader =& $_MIDCOM->get_component_loader();
        $this->_component_path = MIDCOM_ROOT . $loader->path_to_snippetpath($this->_module); 
        $this->module_config = & $this->_load_configuration($this->_component_path);
    }
    /** 
     * Load the configuration for a component, regardless of if this is
     * the current component.
     * @param string the component name (midcom.admin.simplecontent f.x.)
     */
    function &_load_configuration($path)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (is_null($this->_config_snippet_name) || $path == '')
        {
            // Nothing to do.
            return ;
        }
        debug_add( "{$path}/config/{$this->_config_snippet_name}.inc",MIDCOM_LOG_WARN);
        
        // Load and parse the global config
        $data = $this->read_array_from_file("{$path}/config/{$this->_config_snippet_name}.inc");
        if (! $data)
        {
            debug_add("Could not load the file {$path}/config/{$this->_config_snippet_name}.inc, assuming empty default configuration",
                MIDCOM_LOG_WARN);
            $data = Array();
        }
        $config = new midcom_helper_configuration($data);

        // Go for the sitewide default
        $data = $this->read_array_from_file("/etc/midgard/midcom/{$path}/{$this->_config_snippet_name}.inc");
        if ($data !== false)
        {
            $config->store($data, false);
        }

        // Finally, check the sitegroup config
        $data = $this->read_array_from_snippet("{$GLOBALS['midcom_config']['midcom_sgconfig_basedir']}/{$path}/{$this->_config_snippet_name}");
        if ($data !== false)
        {
            $config->store($data, false);
        }

        $config = new midcom_helper_configuration($config->get_all());
        return $config;
        debug_pop();
    }
    /**
     * Get a pointer to the configuration
     * <pre>
     * $this->_config = &$aegir_handler->get_config();
     * </pre>   
     * @return object midgard_helper_configuration
     */
    function &get_config () 
    {
        return $this->module_config;    
    }
    /**
     * Get the current handlers configuration. This function should probably be attached to the
     * handler
     */
    function &get_handler_config($path) {
        $path = str_replace('.', '/', $path);
        return $this->_load_configuration(MIDCOM_ROOT . '/' . $path);
    }
    
    
   /**
     * This helper function reads a file from disk and evaluates its content as array.
     * This is essentially a simple Array($data\n) eval construct.
     *
     * If the file does not exist, false is returned.
     *
     * This function may be called statically.
     *
     * @param string $filename The name of the file that should be parsed.
     * @return Array The read data or false on failure.
     * @see read_array_from_snippet()
     */
    function read_array_from_file ($filename)
    {
        $data = @file_get_contents($filename);
        if ($data === false)
        {
            return false;
        }
        eval ("\$data = Array ({$data}\n);");
        return $data;
    }

    /**
     * This helper function reads a snippet and evaluates its content as array.
     * This is essentially a simple Array($data\n) eval construct.
     *
     * If the snippet does not exist, false is returned.
     *
     * This function may be called statically.
     *
     * @param string $snippetpath The full path to the snippet that should be returned.
     * @return Array The read data or false on failure.
     * @see read_array_from_file()
     */
    function read_array_from_snippet ($snippetpath)
    {
        $snippet = mgd_get_snippet_by_path($snippetpath);
        if ($snippet === false)
        {
            return false;
        }
        eval ("\$data = Array({$snippet->code}\n);");
        return $data;
    }


    /**
     * get the requestswitch for this module.
     * 
     */
    function get_request_switch() 
    {
        return array();
    }

    /**
     * add toolbar items that go across handlers.
     * 
     * Subclass this to get a common toolbar across different handlers.
     */
    function prepare_toolbar() 
    {
        
    }
    
    /*
     * The functions below are utility functions you should not have
     * to change in your subclass.
     */

    /**
     * Get the navigationclass
     * You shouldn't need to extend this if you do not have a severe 
     * need of naming your navigation file something else than
     * aegir_navigation.php.
     * @param none.
     * @return object pointer to the navobject of the module.
     */
    function &get_navigation() 
    {
        if ($this->_nav != null) {
            return $this->_nav;
        }
        
        if ($this->_navigation_class == null) {
            
            $class = $this->_class . '_aegir_navigation';
        } else {
            $class = $this->_navigation_class;
        }
        if (!class_exists($class)) {
                //print_r($this);
                //flush();
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to find navigationclass $class for {$this->_module} Aegir module");
                
        }
        $this->_nav = & new $class();
        
        return $this->_nav;
    }
    
    
    
    /**
     * Get the current leaf
     * @return mixed, string id of the current leaf or null if not set.
     */
    function get_current_leaf() 
    {
        if ($this->_nav === null) {
            $this->get_navigation();
        }
        return $this->_nav->_current_leaf;
    }
    
    /**
     * Set the current node
     * @return mixed string id of the current node or null if not set. 
     */
    function get_current_node() 
    {
        if ($this->_nav === null) {
            $this->get_navigation();
        }
        return $this->_nav->get_current_node();
    }
    /**
     * set the current node should be overwritten by components to determine if the
     * noe is actually a leaf.
     * @param mixed the current node as a midcom_db object or the nodeid.
     * @return void
     */
    function set_current_node ($nodeid) 
    {
        if (is_object ($nodeid)) 
        {
            $nodeid = $nodeid->guid;
        }
        if ($this->_nav === null) {
            $this->get_navigation();
        }
        $this->_nav->_current_node = $nodeid;
    }
    
    /**
     * set the current leaf
     * @param string leafid
     */
    function set_current_leaf ($leafid) {
        if ($this->_nav === null) {
            $this->get_navigation();
        }
        $this->_nav->_current_leaf = $leafid;
    }
    
    /**
     * Load a file in the current module's directory.
     * @param string filename
     */
    function load_component_file( $filename ) 
    {
        require_once (MIDCOM_ROOT ."/" .  $this->_class . "/" . $filename);
    }
    
    /**
     * Helper to generate the breadcrumb array.
     * This function uses current leaf and node to generate the location bar. 
     * @params none
     * @return none
     */
     function generate_location_bar () 
     {
        
        $component_nav = &$this->get_navigation();
        $nodepath = $component_nav->get_breadcrumb_array();
        $toolbars = &midcom_helper_toolbars::get_instance();
        for ($i = count($nodepath) -1; $i >= 0;$i--) {
            $toolbars->aegir_location->add_item(
            array (
                MIDCOM_TOOLBAR_URL => $nodepath[$i][MIDCOM_NAV_URL],
                MIDCOM_TOOLBAR_LABEL => $nodepath[$i][MIDCOM_NAV_NAME],
                MIDCOM_TOOLBAR_HELPTEXT => '',
                MIDCOM_TOOLBAR_ICON => '',
                MIDCOM_TOOLBAR_ENABLED => true,
                MIDCOM_TOOLBAR_HIDDEN => false 
                )
            );
         }
        
     }
     

}
