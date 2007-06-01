<?php
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:cache.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

// Include required files
/** Cache Backend base class */
require_once('cache/backend.php');
/** Cache Module base class */
require_once('cache/module.php');

/**
 * This class is the central access point for all registered caching services. Currently
 * this includes the NAP, Metadata and Page cache databases.
 *
 * The system is two fold:
 *
 * There are cache backends, which are responsible for the actual storage and retrival of
 * cache information, and cache modules, which provide caching services to the application
 * developer.
 *
 * Check the documentation of the backends for configuring the cache on your live site
 * (as an administrator).
 *
 * Check the documentation of the cache modules to learn how to take advantage of the cache
 * services available (as a component/core author).
 *
 * The cache service is independant from the MidCOM Core, as it has to be started up at the
 * beginning of the request. Cache modules are loaded on-demand.
 *
 * This class will be available throught he midcom service getter under the handle cache.
 * The content cache module, for backwards compatibility, will be available as $midcom->cache.
 *
 * All loaded modules will also be available as direct members of this class, you have to ensure
 * the module is loaded in advance though. The class will automatically load all modules which
 * are configured in the autoload_queue in the cache configuration.
 *
 * @package midcom.services
 */
class midcom_services_cache
{

    /**
     * List of all loaded modules, indexed by their class name.
     *
     * @access private
     * @var Array
     */
    var $_modules = Array();

    /**
     * List of all modules in the order they need to be unloaded. This is a FILO queue, the
     * module loaded first is unloaded last.
     *
     * @access private
     * @var Array
     */
    var $_unload_queue = Array();

    function midcom_services_cache()
    {
        // Nothing to do yet.
    }

    /**
     * Cache service startup. It initializes all cache modules configured in the
     * global configuration as outlined in the class introduction.
     *
     * It will load the content cache module as the first one, the rest will be
     * loaded in their order of appearance in the Array.
     */
    function initialize()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        foreach ($GLOBALS['midcom_config']['cache_autoload_queue'] as $name)
        {
            debug_add("Auto-Loading module {$name}", MIDCOM_LOG_INFO);
            $this->load_module($name);
        }

        debug_pop();
    }

    /**
     * Shuts all cache modules down. The content module is explicitly stopped as the
     * last module for clear content cache handling (it might exit()).
     */
    function shutdown()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        foreach ($this->_unload_queue as $name)
        {
            debug_add("Stopping module {$name}", MIDCOM_LOG_INFO);
            $this->_modules[$name]->shutdown();
        }

        debug_pop();
    }

    /**
     * This helper function will load the specified cache module (if not already loaded),
     * add it to the _modules array and assign it to a member variable named after the
     * module.
     *
     * @param string $name The name of the cache module to load.
     */
    function load_module($name)
    {
        if (array_key_exists($name, $this->_modules))
        {
            return;
        }

        $filename = "cache/module/{$name}.php";
        $classname = "midcom_services_cache_module_{$name}";

        require_once($filename);
        if (! class_exists($classname))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Tried to load the cache module {$name}, but the class {$classname} was not found in the file {$filename}");
            // This will exit.
        }

        $this->_modules[$name] = new $classname();
        $this->_modules[$name]->initialize();
        $this->$name =& $this->_modules[$name];
        array_unshift($this->_unload_queue, $name);
    }

    /**
     * Invalidate all caches completly.
     *
     * Use this, if you have, f.x. changes in the layout. The URL function
     * midcom-cache-invalidate will trigger this function.
     */
    function invalidate_all()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        foreach ($this->_unload_queue as $name)
        {
            debug_add("Invalidating the cache module {$name} completly.");
            $this->_modules[$name]->invalidate_all();
        }

        debug_pop();
    }

    /**
     * Invalidates all cache records accociated with a given content object.
     *
     * @param mixed $guid This is either a GUID or a MidgardObject, in which case the Guid is auto-dtermined.
     * @param string $skip_module If specified, the module mentioned here is skipped during invalidation.
     *     This option <i>should</i> be avoided by component authors at all costs, it is there for
     *     optimizations within the core cache modules (which sometimes need to invalidate only other
     *     modules, and invalidate themselves implicitly).
     */
    function invalidate($guid, $skip_module = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (is_object($guid))
        {
            debug_add_type ("Got an object, trying to auto-detect the GUID. Passed type was:", $guid);
            $guid = $guid->guid();
        }
        foreach ($this->_unload_queue as $name)
        {
            if ($name == $skip_module)
            {
                debug_add("We have to skip the cache module {$name}.");
                continue;
            }
            debug_add("Invalidating the cache module {$name} for GUID {$guid}.");
            $this->_modules[$name]->invalidate($guid);
        }
        debug_pop();
    }
}

/**
 * Global instance of the Caching service. This is also available as $midcom->cache.
 *
 * @global midcom_services_cache $GLOBALS['midcom_cache']
 */
$GLOBALS['midcom_cache'] = null;

/**
 * Helper function, starts up the caching system, assigns it to the global
 * variable $GLOBALS['midcom_cache'].
 */
function midcom_services_cache_startup()
{
    $GLOBALS['midcom_cache'] = new midcom_services_cache();
    $GLOBALS['midcom_cache']->initialize();
}

?>