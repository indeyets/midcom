<?php
/**
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:_componentloader.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This class is a Factory that is responsible for loading and
 * establishing the interface to a MidCOM Component.
 *
 * <b>Working with components</b>
 *
 * Normally, two things are important when you deal with other components:
 *
 * First, if you want to list other components, or for example check whether they
 * are available, you should use the component manifest listing, known as $manifests.
 * It gives you all meta-information about the components.
 *
 * This should actually suffice for most normal operations.
 *
 * If you develop framework tools (like administration interfaces, you will also
 * need access to the component interface class, which can be obtained by
 * get_component_class(). This class is derived from the component interface
 * baseclass and should give you everything you need to work with the component
 * and its information itself.
 *
 * Other then that, you should not have to deal with the components, perhaps with
 * the only exception of is_loaded() and load() to ensure other components are loaded
 * in case you need them and they are not a pure-code library.
 *
 *
 * <b>Loading components</b>
 *
 * When the component loader receives a request it roughly works in
 * three stages:
 *
 * 1. Verify that the given component is valid in terms of the
 *    MidCOM Specification. This will check the existence of all
 *    required SnippetDirs.
 * 2. Load all Snippets related with the MidCOM Interface Concept
 *    Classes and instantiate the MidCOM and Component concept
 *    classes, initialize the Component. Check whether all
 *    required concept classes exist.
 * 3. Return the various interface concepts upon each request
 *    from the framework.
 *
 * Stage 1 will do all basic sanity checking possible before
 * loading any snippets. It will check for the existence of all
 * defined sub-SnippetDirs that are required for the system to
 * work. If anything is missing, step 1 fails and the
 * componentloader refuses to load the component.
 *
 * Stage 2 will then load the interfaces.php file from the midcom
 * directory. The existence of all required Interface classes is
 * then checked. If this check is successful, the concrete classes
 * of the various interface concepts are instantiated and stored
 * internally. The component is initialized by the call to
 * MIDCOM::initialize() which should load everything necessary.
 *
 * Stage 3 is the final stage where the loader stays in memory in
 * order to return references (!) to the loaded component's
 * Interface Classes upon request.
 *
 * In case you need an instance of the component loader to verify or
 * transform component paths, use the function
 * midcom_application::get_component_loader, which returns a
 * <i>reference</i> to the loader.
 *
 * @package midcom
 * @see midcom_application::get_component_loader()
 */
class midcom_helper__componentloader
{
    /**
     * This indexed array stores the MidCOM paths of all loaded
     * components. Its elements are used as keys for the cache storage.
     *
     * @var Array
     * @access private
     */
    var $_loaded = Array();

    /**
     * This array contains a list of components that were tried to be loaded.
     * The components are added to this list *even* if the system only tried
     * to load it and failed. This way we protect against duplicate class errors
     * and the like if a defective class is tried to be loaded twice.
     *
     * The array maps component names to loading results. The loading result is
     * either false or true as per the result of the load call.
     *
     * @var Array
     * @access private
     */
    var $_tried_to_load = Array();

    /**
     * This is a part of the component cache. It stores references to
     * the COMPONENT classes of the different loaded components, indexed by
     * their MidCOM Path.
     *
     * @var Array
     * @access private
     */
    var $_component_classes = Array();

    /**
     * This is a part of the component cache. It stores references to
     * the Content Administration variant of the COMPONENT classes of the
     * different loaded components, indexed by their MidCOM Path.
     *
     * @var Array
     * @access private
     */
    var $_contentadmin_classes = Array();

    /**
     * This is a part of the component cache. It stores the properties
     * of the already loaded components.
     *
     * @var Array
     * @access private
     */
    var $_component_properties = Array();

    /**
     * This is a part of the component cache. It stores references to
     * the interface classes of the different loaded components, indexed by
     * their MidCOM Path.
     *
     * @var Array
     * @access private
     * @see midcom_baseclasses_components_interface
     */
    var $_interface_classes = Array();

    /**
     * This is a part of the component cache. It stores references to
     * the MIDCOM classes of the different loaded component, indexed by
     * their MidCOM Path.
     *
     * @var Array
     * @access private
     * @deprecated This has been deprecated in MidCOM 2.4 in favor of the new component interface classes.
     */
    var $_midcom_classes = Array();

    /**
     * This is a part of the component cache. It stores references to
     * the OUTPUT classes of the different loaded component, indexed by
     * their MidCOM Path.
     *
     * @var Array
     * @access private
     * @deprecated This has been deprecated in MidCOM 2.4 in favor of the new component interface classes.
     */
    var $_output_classes = Array();

    /**
     * This is a part of the component cache. It stores references to
     * the NAP classes of the different loaded component, indexed by
     * their MidCOM Path.
     *
     * @var Array
     * @access private
     * @deprecated This has been deprecated in MidCOM 2.4 in favor of the new component interface classes.
     */
    var $_nap_classes = Array();

    /**
     * This lists all available components in the systems in the form of their manifests,
     * indexed by the component name. Whenever possible you should refer to this listing
     * to gain information about the components available.
     *
     * This information is loaded during startup.
     *
     * @var array
     * @see midcom_core_manifest
     */
    var $manifests = Array();

    /**
     * This array contains all registered MidCOM operation watches. They are indexed by
     * operation and map to components / libraries which have registered to classes.
     * Values consist of an array whose first element is the component and subsequent
     * elements are the types involved (so a single count means all objects).
     *
     * @var array
     * @access private
     */
    var $_watches = Array(
        MIDCOM_OPERATION_DBA_CREATE => Array(),
        MIDCOM_OPERATION_DBA_UPDATE => Array(),
        MIDCOM_OPERATION_DBA_DELETE => Array(),
    );

    /**
     * This is an array containing a list of watches that need to be executed at the end
     * of any given request. The array is indexed by artificial keys constructed out of the
     * watched object's class type and guid values. The array always contain the object
     * instance in the first element, and all components that need to be notified in the
     * subsequent keys.
     *
     * @var array
     * @access private
     */
    var $_watch_notifications = Array(
        MIDCOM_OPERATION_DBA_CREATE => Array(),
        MIDCOM_OPERATION_DBA_UPDATE => Array(),
        MIDCOM_OPERATION_DBA_DELETE => Array(),
    );

    /**
     * The constructor will initialize the class. Nothing special is
     * done here. The real initialization is done in initialize() so that
     * we already have a reference to ourselves.
     */
    function midcom_helper__componentloader()
    {
        // Empty.
    }

    /**
     * This function will invoke _load directly. If the loading process
     * is unsuccessful, it will call generate_error.
     *
     * @param string $path    The component to load explicitly.
     */
    function load($path)
    {
        if (! $this->_load($path))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to load the component {$path}, see the debug log for more information");
        }
    }

    /**
    * This function will invoke _load directly. If the loading process
    * is unsuccessful, false is returned.
    *
    * @param string $path    The component to load explicitly.
    * @return boolean Indicating success.
    */
    function load_graceful($path)
    {
        return $this->_load($path);
    }

    /**
     * This function will load the component specified by the MidCOM
     * path $path. If the component could not be loaded successfully due
     * to integrity errors (missing SnippetDirs, Classes, etc.), it will
     * return false and populate $GLOBALS['midcom_errstr'] accordingly.
     *
     * @param string $path    The component to load.
     * @return boolean Indicating success.
     * @access private
     */
    function _load($path)
    {
        $GLOBALS['midcom_errstr'] = '';
                        
        if (empty($path))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("No path given, aborting");
            debug_pop();
            $GLOBALS['midcom_errstr'] = 'No component path given.';
            return false;
        }

        // Check if this component is already loaded...
        if (array_key_exists($path, $this->_tried_to_load))
        {
            /*
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("We have already tried to load {$path}, returning original result.");
            debug_pop();
            */
            $GLOBALS['midcom_errstr'] = "Component {$path} already loaded.";
            return $this->_tried_to_load[$path];
        }

        // Flag this path as loaded/failed, we'll set this flag to true when we reach
        // the end of this call.
        $this->_tried_to_load[$path] = false;

        // Check if the component is listed in the class manifest list. If not,
        // we immediately bail - anything went wrong while loading the component
        // (f.x. broken DBA classes).
    //echo"'".$path."'";
    //print_r($this->manifests);
        if (! array_key_exists($path, $this->manifests))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The component {$path} was not found in the manifest list. Cannot load it.",
                MIDCOM_LOG_WARN);
            debug_pop();
            $GLOBALS['midcom_errstr'] = 'Component not in manifest list.';
            return false;
        }

        // Validate and translate url
        if (! $this->validate_url($path))
        {
            $GLOBALS['midcom_errstr'] = 'Component URL not valid.';
            return false;
        }
        $snippetpath = $this->path_to_snippetpath($path);

        if (! $this->validate_path($snippetpath))
        {
            $GLOBALS['midcom_errstr'] = 'Component path not valid.';
            return false;
        }

        // Load Snippets
        $directory = MIDCOM_ROOT . "{$snippetpath}/midcom";
        if (! is_dir($directory))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            $GLOBALS['midcom_errstr'] = "Failed to access Snippetdir {$directory}: Directory not found.";
            debug_add($GLOBALS['midcom_errstr'], MIDCOM_LOG_CRIT);
            debug_pop();
            $GLOBALS['midcom_errstr'] = 'Directory not found.';
            return false;
        }

        // Load the interfaces.php snippet, abort if that file is not available.
        if (! file_exists("{$directory}/interfaces.php"))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            $GLOBALS['midcom_errstr'] = "File {$directory}/interfaces.php is not present.";
            debug_add($GLOBALS['midcom_errstr'], MIDCOM_LOG_CRIT);
            debug_pop();
            $GLOBALS['midcom_errstr'] = 'Missing interfaces class.';
            return false;
        }
        require("{$directory}/interfaces.php");

        // Load the component interface, try to be backwards-compatible
        $prefix = $this->snippetpath_to_prefix($snippetpath);

        $compat = false;
        if (class_exists("{$prefix}_interface"))
        {
            $classname = "{$prefix}_interface";
            $this->_interface_classes[$path] = new $classname();
            $this->_midcom_classes[$path] =& $this->_interface_classes[$path];
        }
        else
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            $GLOBALS['midcom_errstr'] = "Class {$prefix}_interface does not exist.";
            debug_add($GLOBALS['midcom_errstr'], MIDCOM_LOG_CRIT);
            debug_pop();
            $GLOBALS['midcom_errstr'] = 'No interface class defined.';
            return false;
        }

        // Make DBA Classes known, bail out if we encounter an invalid class
        foreach ($this->manifests[$path]->class_definitions as $file)
        {
            if (! $_MIDCOM->dbclassloader->load_classes($this->manifests[$path]->name, $file))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to load the component manifest for {$this->manifests[$path]->name}: The DBA classes failed to load.", MIDCOM_LOG_WARN);
                debug_pop();
                return false;
            }
        }

        $comp_init_class =& $this->_midcom_classes[$path];
        if ($comp_init_class->initialize(false) == false)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            $GLOBALS['midcom_errstr'] = "Initialize of Component {$path} failed.";
            debug_add($GLOBALS['midcom_errstr'], MIDCOM_LOG_CRIT);
            debug_pop();
            $GLOBALS['midcom_errstr'] = 'Initialization failed.';
            return false;
        }

        // New mode
        $this->_component_classes[$path] =& $comp_init_class;
        $this->_contentadmin_classes[$path] =& $comp_init_class;
        $this->_nap_classes[$path] =& $comp_init_class;

        $this->_loaded[] = $path;

        // If this is no pure-code library and a legacy component
        if (   ! $this->manifests[$path]->purecode
            && $compat)
        {
            if (! class_exists ($prefix . "_component"))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                $GLOBALS['midcom_errstr'] = "Class " . $prefix . "_component does not exist.";
                debug_add($GLOBALS['midcom_errstr'], MIDCOM_LOG_CRIT);
                debug_pop();
                return false;
            }

            if (! class_exists ($prefix . "_nap"))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                $GLOBALS['midcom_errstr'] = "Class " . $prefix . "_nap does not exist.";
                debug_add($GLOBALS['midcom_errstr'], MIDCOM_LOG_CRIT);
                debug_pop();
                return false;
            }
        }
        $this->_tried_to_load[$path] = true;

        return true;
    }

    /**
     * Returns TRUE if the component identified by the MidCOM path $url
     * is already loaded and available for usage.
     *
     * @param string $path    The component to be queried.
     * @return boolean            true if it is loaded, false otherwise.
     */
    function is_loaded($path)
    {
        return in_array($path, $this->_loaded);
    }

    /**
     * Returns a reference to an instance of the specified component's
     * MIDCOM class. The component is given in $path as a MidCOM path.
     * Such an instance will be cached by the framework so that only
     * one instance is always active for each component. Missing
     * components will be dynamically loaded into memory.
     *
     * @param string $path    The component name.
     * @return mixed        A reference to the concept class in question.
     * @deprecated This has been deprecated in MidCOM 2.4 in favor of the new component interface classes.
     */
    function & get_midcom_class ($path)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Warning, this method is deprecated, you should use the new component interface instead.",
            MIDCOM_LOG_DEBUG);
        debug_pop();

        if (! $this->is_loaded($path))
        {
            if (!$this->_load($path))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, $GLOBALS["midcom_errstr"]);
            }
        }

        return $this->_midcom_classes[$path];
    }

    /**
     * Returns a reference to an instance of the specified component's
     * interface class. The component is given in $path as a MidCOM path.
     * Such an instance will be cached by the framework so that only
     * one instance is always active for each component. Missing
     * components will be dynamically loaded into memory.
     *
     * @param string $path    The component name.
     * @return midcom_baseclasses_components_interface A reference to the concept class in question or NULL if
     *     the class in question does not yet support the new Interface system.
     */
    function & get_interface_class ($path)
    {
        if (! $this->is_loaded($path))
        {
            if (!$this->_load($path))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to load component {$path}: {$GLOBALS['midcom_errstr']}");
            }
        }

        return $this->_interface_classes[$path];
    }

    /**
     * Returns a property value for the component identified by the
     * MidCOM Path $path and the property key $key. It will return NULL
     * if the property key is unknown. Remember to make a type sensitive
     * comparison here. If the component is not loaded, the framework
     * tries to load it, see _componentloader::load for further details.
     *
     * This is a compatibility implementation until the component manifest has been
     * introduced everywhere.
     *
     * @param string $component The component name.
     * @param int $key The property being queried.
     * @return mixed The property value.
     * @deprecated This function maps to the component Manifest as of 2005-09-08 and should no longer be used directly.
     */
    function get_component_property($component, $key)
    {
        debug_add('Use of deprecated function ' . __CLASS__ . '::' . __FUNCTION__, MIDCOM_LOG_DEBUG);
        if (! array_key_exists($component, $this->manifests))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The manifest for the component {$component} was not found.");
            debug_pop();
            return null;
        }
        switch ($key)
        {
            case MIDCOM_PROP_NAME:
                return $this->manifests[$component]->get_name_translated();

            case MIDCOM_PROP_PURECODE:
                return $this->manifests[$component]->purecode;

            case MIDCOM_PROP_VERSION:
                return $this->manifests[$component]->version;

            case MIDCOM_PROP_ACL_PRIVILEGES:
                return $this->manifests[$component]->privileges;

            default:
                return null;
        }
    }

    /**
     * Helper, converting a component path (net.nehmer.blog)
     * to a snippetpath (/net/nehmer/blog).
     *
     * @param string $path    Input string.
     * @return string        Converted string.
     */
    function path_to_snippetpath($path)
    {
        return "/" . strtr($path, ".", "/");
    }

    /**
     * Helper, converting a snippetpath (/net/nehmer/blog)
     * to a class prefix (net_nehmer_blog).
     *
     * @param string $snippetpath    Input string.
     * @return string                Converted string.
     */
    function snippetpath_to_prefix ($snippetpath)
    {
        return substr(strtr($snippetpath, "/", "_"), 1);
    }

    /**
     * Helper, converting a component path (net.nehmer.blog)
     * to a class prefix (net_nehmer_blog).
     *
     * @param string $path    Input string.
     * @return string        Converted string.
     */
    function path_to_prefix ($path)
    {
        return strtr($path, ".", "_");
    }

    /**
     * validate_path is used to validate the component located at the
     * snippetdir Path $snippetpath. This is a fully qualified snippetdir path
     * to the component in question.
     *
     * @todo Currently partly disabled due to the FS-Transition.
     * @param string $snippetpath    The path to be checked.
     * @return boolean                 True if valid, false otherwise.
     */
    function validate_path($snippetpath)
    {
        $directory = MIDCOM_ROOT . $snippetpath;

        if (! is_dir($directory))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            $GLOBALS['midcom_errstr'] = "Failed to validate the component path {$directory}: It is no directory.";
            debug_add($GLOBALS['midcom_errstr'], MIDCOM_LOG_CRIT);
            debug_pop();
            return false;
        }

        return true;
    }

    /**
     * Will validate the given MidCOM Path $path for syntactical
     * correctness. Currently this is a RegEx check that checks for
     * invalid characters in $path, so validate_url does explicitly
     * <i>not</i> check whether the referenced component does exist and
     * whether it is structurally valid.
     *
     * @param string $path    The path to be checked.
     * @return boolean         True if valid, false otherwise.
     */
    function validate_url($path)
    {
        global $midcom;

        if (! ereg ("^[a-z][a-z0-9\.]*[a-z0-9]$", $path))
        {
            $GLOBALS['midcom_errstr'] = "Invalid URL: " . $path;
            debug_push("midcom_helper__componentloader::validate_url");
            debug_add($GLOBALS['midcom_errstr'], MIDCOM_LOG_CRIT);
            debug_pop();
            return false;
        }

        return true;
    }

    /**
     * Retrieve a list of all loaded components. The Array will contain an
     * unsorted collection of MidCOM Paths.
     *
     * @return Array    List of loaded components
     */
    function list_loaded_components()
    {
        return $this->_loaded;
    }

    /**
     * This function tries to load all available components by traversing
     * the complete(!) Snippetdir Tree. Note, that this might take some time,
     * so use it carefully. After this function returns you can get a list
     * of all available and valid component paths' through
     * list_loaded_components. It will only query the root snippetdirs matching
     * the known top-level-domains (two-letter ones, com, net, org, name, biz,
     * museum). This is done to speed up processing as Asgard or Nemein.Net for
     * example clutter the SG0 with lots of snippetdirs that are no MidCOM
     * components. Note that the load_all command also iterates through the
     * MidCOM tree.
     *
     * <b>Important note:</b> With the introduction of the component manifest system,
     * calling load_all should no longer be necessary, as (as far as I think) all
     * issues which originally required load_all can be resolved using the manifest.
     * If you think you have found a case where this is not true, please contact
     * the developers on the MidCOM list.
     *
     * @see midcom_core_manifest
     */
    function load_all()
    {
        static $load_all_has_executed = false;

        debug_push_class(__CLASS__, __FUNCTION__);

        if ($load_all_has_executed)
        {
            debug_add("We have loaded all components already, returning without doing anything therefore.");
            debug_pop();
            return;
        }

        foreach ($this->manifests as $name => $manifest)
        {
            if (! $this->_load($name))
            {
                debug_add("Failed to load the component {$name}, skipping silently.", MIDCOM_LOG_INFO);
            }
        }

        debug_print_r('We have attempted to load all components, we ended up with these loaded ones:', $this->_loaded);
        $load_all_has_executed = true;
        debug_pop();
    }

    /**
     * This function is called during system startup and loads all component manifests from
     * the disk. The list of manifests to load is determined using a find shell call and is cached
     * using the phpscripts cache module.
     *
     * This method is executed during system startup by the framework. Other parts of the system
     * must not access it.
     */
    function load_all_manifests()
    {
        $cache_identifier = $_MIDCOM->cache->phpscripts->create_identifier('midcom.componentloader', 'manifests');

        if (! $cache_identifier)
        {
            $cache_hit = false;
        }
        else
        {
            $cache_hit = $_MIDCOM->cache->phpscripts->load($cache_identifier, filemtime(__FILE__));
        }

        if (! $cache_hit)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Cache miss, generating component manifest cache now.');
            $this->_generate_class_manifest_cache($cache_identifier);
            debug_pop();
        }
    }

    /**
     * This function is called from the class manifest loader in case of a cache
     * miss.
     *
     * @param string $cache_identifier The cache identifier to use to cache the manifest
     *     loading queue.
     * @todo investigate if we should unset the package.xml part of the arrays and serialize them
     */
    function _generate_class_manifest_cache($cache_identifier)
    {
        // First, we locate all manifest includes:
        // We use some find construct like find -follow -type d -name "config"
        // This does follow symlinks, which can be important when several
        // CVS commits are "merged" manually
        $directories = Array();
        exec('find ' . MIDCOM_ROOT . ' -follow -type d -name "config"', $directories);
        $code = "";
        foreach ($directories as $directory)
        {
            $filename = "{$directory}/manifest.inc";
            if (file_exists($filename))
            {
                $manifest_data = file_get_contents($filename);
                
                $code .= "\$_MIDCOM->componentloader->load_manifest(
                    new midcom_core_manifest(    
                    '{$filename}', array({$manifest_data})));\n";
            }
        }

        if (! $_MIDCOM->cache->phpscripts->add($cache_identifier, $code))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to add the manifest loading queue to the cache using the identifier {$cache_identifier}.",
                MIDCOM_LOG_WARN);
            debug_add('Continuing uncached.');
            debug_pop();
            eval($code);
        }
        return;
    }

    /**
     * Load a manifest from the disk and add it to the $manifests list.
     *
     * It already does all necessary registration work:
     *
     * - All default privileges are made known
     * - All defined DBA class sets are loaded. If an error occurs here,
     *   the manifest will be ignored and an error is logged.
     *
     *  @param object $manifest the manifest object to load.
     */
    function load_manifest($manifest)
    {
        //echo "Load $manifest->name";
        $this->manifests[$manifest->name] = $manifest;

        // Register Privileges
        $_MIDCOM->auth->register_default_privileges($manifest->privileges);

        // Register watches
        if ($manifest->watches !== null)
        {
            foreach ($manifest->watches as $watch)
            {
                // Check for every operation we know and register the watches.
                // We make shortcuts for less typing.
                $operations = $watch['operations'];
                $watch_info = $watch['classes'];
                if ($watch_info === null)
                {
                    $watch_info = Array();
                }

                // Add the component name into the watch information, it is
                // required for later processing of the watch.
                array_unshift($watch_info, $manifest->name);

                foreach ($this->_watches as $operation_id => $ignore)
                {
                    // Check whether the operations flag list from the component
                    // contains the operation_id we're checking a watch for.
                    if ($operations & $operation_id)
                    {
                        $this->_watches[$operation_id][] = $watch_info;
                    }
                }
            }
        }
    }

    /**
     * This is called by the framework whenever watchable events occur.
     * The object referenced by $object may be null where appropriate for
     * the operation in question, it is not taken by reference.
     *
     * Call this only if the operation in question has completed successfully.
     *
     * The component handlers can safely assume that it is only called once per object
     * and operation at the end of the request.
     *
     * This latter fact is important to understand: Watches are not executed immediately,
     * instead, they are collected throughout the request and executed during
     * midcom_application::finish() exactly once per instance. The instance is refreshed
     * before it is actually sent to the watchers using a refresh member function unless
     * the object has been deleted, then there will be no refresh attempt.
     *
     * An instance in this respect is a unique combination of class type and guid values.
     *
     * A watchable object must therefore have the following properties:
     *
     * - <i>string $guid</i> The guid identifying the object.
     * - <i>boolean refresh()</i> A method used to refresh the object against its datasource.
     *
     * So, two instances are equal <i>if and only if</i> they are of the same class and
     * have the same $guid property value.
     *
     * @param int $operation The operation that has occurred.
     * @param mixed $object The object on which the operation occurred. The system will
     *     do is_a checks against any registered class restriction on the watch. The object
     *     is not taken by-reference but refreshed before actually executing the hook at the
     *     end of the request.
     */
    function trigger_watches($operation, $object)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        // We collect the components of all watches here, so that we can
        // unique-out all duplicates before actually calling the handler.
        $components = Array();
        foreach ($this->_watches[$operation] as $watch)
        {
            if (count($watch) == 1)
            {
                $components[] = $watch[0];
            }
            else
            {
                $component = array_shift($watch);
                foreach ($watch as $classname)
                {
                    if (is_a($object, $classname))
                    {
                        $components[] = $component;
                        break;
                    }
                }
            }
        }

        $components = array_unique($components);
        $object_key = get_class($object) . $object->guid;
        if (! array_key_exists($object_key, $this->_watch_notifications[$operation]))
        {
            $this->_watch_notifications[$operation][$object_key] = Array($object);
        }
        foreach ($components as $component)
        {
            // FIXME: This causes a PHP notice
            error_reporting(E_WARNING);
            if (! in_array($component, $this->_watch_notifications[$operation][$object_key]))
            {
                $this->_watch_notifications[$operation][$object_key][] = $component;
            }
            error_reporting(E_ALL);
        }
        debug_pop();
    }

    /**
     * This function processes all pending notifies and flushes the pending list.
     * It is called automatically during MidCOM shutdown at the end of the request.
     *
     * All Notifies for objects which can't be refreshed will be ignored silently
     * (but logged of course). Deleted objects are of course not refreshed.
     *
     * This function can only be called once during a request.
     */
    function process_pending_notifies()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if ($this->_watch_notifications === null)
        {
            debug_add('Pending notifies should only be processed once at the end of the request, aborting.', MIDCOM_LOG_WARN);
            debug_pop();
            return;
        }

        foreach ($this->_watch_notifications as $operation => $operation_data)
        {
            foreach ($operation_data as $tmp => $data)
            {
                $object = array_shift($data);
                if ($operation != MIDCOM_OPERATION_DBA_DELETE)
                {
                    // Only refresh when we haven't deleted the record.
                    if (! $object->refresh())
                    {
                        debug_add('Failed to refresh an object before notification, skipping it. see the debug level log for a dump.', MIDCOM_LOG_WARN);
                        debug_print_r('Object dump:', $object);
                        continue;
                    }
                }
                foreach ($data as $component)
                {
                    if (! $this->is_loaded($component))
                    {
                        // Try to load the component, fail silently if we can't load the component
                        if (! $this->_load($component))
                        {
                            debug_add("Failed to load the component {$component} required for handling the current watch set, skipping watch.", MIDCOM_LOG_INFO);
                            continue;
                        }
                    }
                    $this->_interface_classes[$component]->trigger_watch($operation, $object);
                }
            }
        }
        $this->_watch_notifications = null;
        debug_pop();
    }

    /**
     * This small helper builds a complete set of custom data associated with a given component
     * identifier. In case a given component does not have the key set and the boolean parameter
     * is set to true, an empty array is added implicitly.
     *
     * @param string $component The custom data component index to look for.
     * @param boolean $showempty Set this flag to true to get an (empty) entry for all components which
     *     don't have customdata applicable to the component index given. This is disabled by default.
     * @return Array All found component data indexed by known components.
     */
    function get_all_manifest_customdata($component, $showempty = false)
    {
        $result = Array();
        foreach ($this->manifests as $manifest)
        {
            if (array_key_exists($component, $manifest->customdata))
            {
                $result[$manifest->name] = $manifest->customdata[$component];
            }
            else if ($showempty)
            {
                $result[$manifest->name] = Array();
            }
        }
        return $result;
    }

    /**
     * Get list of component and its dependencies depend on
     *
     * @param string $component Name of a component
     * @return array List of dependencies
     */
    public function get_component_dependencies($component)
    {
        static $checked = null;
        if (is_null($checked))
        {
            $checked = array();
        }
        if (isset($checked[$component]))
        {
            return array();
        }
        $checked[$component] = true;

        if (!isset($this->manifests[$component]))
        {
            return array();
        }

        $dependencies = array();

        if (   !isset($this->manifests[$component]->_raw_data['package.xml'])
            || !isset($this->manifests[$component]->_raw_data['package.xml']['dependencies']))
        {
            return $dependencies;
        }

        foreach ($this->manifests[$component]->_raw_data['package.xml']['dependencies'] as $dependency => $dependency_data)
        {
            if (   isset($dependency_data['channel'])
                && $dependency_data['channel'] != $GLOBALS['midcom_config']['pear_channel'])
            {
                // We can ignore non-component dependencies
                continue;
            }
            
            if ($dependency == 'midcom')
            {
                // Ignore
                continue;
            }
            
            $dependencies[] = $dependency;
            $subdependencies = $this->get_component_dependencies($dependency);
            $dependencies = array_merge($dependencies, $subdependencies);
        }

        return array_unique($dependencies);
    }

    /**
     * Checks if component is a part of the default MidCOM distribution
     * or an external component
     *
     * @param string $component Component to check
     */
    public function is_core_component($component)
    {
        static $core_components = null;
        if (is_array($core_components))
        {
            if (in_array($component, $core_components))
            {
                return true;
            }
            return false;
        }

        // TODO: Put this into a centralized location
        $core_components = array
        (
            'midcom',
            'midcom.core.nullcomponent',
            // From midcom dependencies
            'midcom.admin.babel',
            'midcom.admin.folder',
            'midcom.admin.help',
            'midcom.admin.settings',
            'midcom.admin.styleeditor',
            'midcom.admin.user',
            'midgard.admin.asgard',
            'no.bergfald.rcs',
            // From task_midgardcms dependencies
            'net.nehmer.blog',
            'net.nemein.calendar',
            'net.nemein.personnel',
            'midcom.helper.imagepopup',
            'midcom.helper.search',
            'de.linkm.sitemap',
            'net.nehmer.static',
            'fi.protie.navigation',
        );

        // Gather dependencies too
        $dependencies = array();
        foreach ($core_components as $component)
        {
            $component_dependencies = $this->get_component_dependencies($component);
            $dependencies = array_merge($dependencies, $component_dependencies);
        }
        $core_components = array_unique(array_merge($core_components, $dependencies));

        if (in_array($component, $core_components))
        {
            return true;
        }

        return false;
    }

    public function get_component_icon($component)
    {
        if ($component == 'midcom')
        {
            return 'stock-icons/logos/midgard-16x16.png';
        }

        if (!isset($this->manifests[$component]))
        {
            return null;
        }

        if (isset($this->manifests[$component]->_raw_data['icon']))
        {
            return $this->manifests[$component]->_raw_data['icon'];
        }

        return 'stock-icons/16x16/package.png';
    }
}

?>