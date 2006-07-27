<?php
/**
 * Created on Sep 3, 2005
 * @author tarjei huse
 * @package midcom.admin.aegir
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 *
 * This class provides simple
 *
 */

class midcom_admin_core_config extends midcom_baseclasses_components_purecode {

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
     * Pointer to the local module configuration
     * @var midcom_helper_configuration
     * @access public
     */
     var $module_config = null;


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
     * Constructur function.
     * @var string name of midcom
     */
    function midcom_admin_core_config ( $midcom )
    {
        $this->_module = $midcom;
    }
    /**
     * loads the files needed, initiates various variables.
     *
     * */
    function initialize()
    {
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
        if (is_null($this->_config_snippet_name) || $path == '')
        {
            // Nothing to do.
            return ;
        }
        debug_push_class(__CLASS__, __FUNCTION__);
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
        debug_pop();
        return $config;
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
    function &get_handler_config($path)
    {
        $path = str_replace('.', '/', $path);
        $config = $this->_load_configuration(MIDCOM_ROOT . '/' . $path);
        return $config;
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
        $nav = & new midcom_helper_nav();
        return $nav;
    }

    /**
     * Stub functions used by Aegir
     */
    function set_current_node($nodeid) {}



    /**
     * Load a file in the current module's directory.
     * @param string filename
     */
    function load_component_file( $filename )
    {
        require_once (MIDCOM_ROOT ."/" .  $this->_class . "/" . $filename);
    }


}
?>
