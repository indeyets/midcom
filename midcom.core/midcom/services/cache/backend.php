<?php
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:backend.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the base class of the MidCOM Cache backend infrastructure. It provides a general
 * interface for the caching services by encapsulating the calls specific to the data
 * storage interface.
 * 
 * Each cache database in use is encapsulated by its own instance of this service, identified
 * by their name and the handler which controls it. The name must be unique throughout the
 * entire server. See Namespacing below for details.
 * 
 * A handlers type is identified by its class name, so midcom_sercices_cache_backend_dba is 
 * the DBA handler from the original pre 2.4 MidCOM.
 * 
 * <b>Inter-Process synchronization:</b>
 *
 * Backend drivers have to ensure they can be accessed concurrently to ensure Database 
 * integrity.
 * 
 * <b>Resource handles:</b>
 * 
 * Resource handles (for example for DBA access) should be closed if necessary if they
 * would block other processes. It is unknown to me if such handles would be safe to use 
 * over several requests. 
 * 
 * <b>Namespacing:</b>
 * 
 * Each cache database in use has a name, which must consist only of characters valid for 
 * file names on the current system. You may create any file or directory within the midcom
 * cache directory as long as you use your name as a prefix. 
 * 
 * If you want to stay on the safe side, only cache names using the characters matching the
 * regex class [a-zA-Z0-9._-] should be used. 
 * 
 * <b>General configuration directives:</b>
 * 
 * - <i>string directory</i>: The subdirectory in the cache's base directory to use by this
 *   backend. This is automatically concatenated with the systemwide cache base directory.
 * - <i>string driver</i>: The concrete class instance to create. It must match the name of
 *   a file within the backend directory. Note, that the class must also be named accordingly,
 *   the driver "dba" is searched in the file "backend/dba.php" and must be of the type
 *   "midcom_services_cache_backend_dba".
 * - <i>boolean auto_serialize</i>: Set this to true to enable automatic serialization on storage
 *   and/or retrieval. Disabled by default.
 * 
 * @package midcom.services
 */

class midcom_services_cache_backend
{
    /**#@+
     * Configuration variable
     *
     * @access protected
     */
   
    /**
     * The backend instance name. This variable my not be written to
     * after the instance has been created.
     * 
     * @var string
     */
    var $_name = null;
    
    /**
     * Configuration key array. This is populated during initialize().
     * 
     * @var Array
     */
    var $_config = null;
    
    /**
     * The base directory in which we may add files and directories within our namespace.
     * 
     * @var string
     */
    var $_cache_dir = null;
    
    /**
     * Set this to true if you plan to store PHP data structures rather then strings, the
     * interface will automatically serialize/unserialize the data you store/retrieve
     * from the database.
     * 
     * @var boolean
     */
    var $_auto_serialize = false;
    
    /**#@-*/

    /**#@+
     * Internal state variable
     *
     * @access private
     */
    
    /**
     * True, if the database has been opened for reading previously. This is also
     * true, if we are in read-write mode, naturally.
     * 
     * Therefore, this flag is also used for checking whether the database is open
     * in general.
     * 
     * @var boolean
     */
    var $_open_for_reading = false;
    
    /**
     * True, if the database has been opened for writing previously.
     * 
     * @var boolean
     */
    var $_open_for_writing = false;
    
    /**#@-*/

    
    /**
     * The constructor just initializes the empty object. The actual initialization
     * is done by the initialize() event which does the actual configuration.
     */
    function midcom_services_cache_backend()
    {
        // Nothing to do yet.
    }
    
    /**
     * Initializes the backend by acquiring all necessary information required for
     * runtime. 
     * 
     * After base class initialization, the event handler _on_initialize is called,
     * in which all backend specific stuff should be done.
     * 
     * @param string $name The name ("identifier") of the handler instance.
     * @param Array $config The configuration to use.
     */
    function initialize($name, $config)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $this->_name = $name;
        if (is_array($config))
        {
            $this->_config = $config;
        }
        else
        {
            $this->_config = Array($config);
        }
        if (! array_key_exists('directory', $this->_config))
        {
            $this->_config['directory'] = '';
        }
        if (array_key_exists('auto_serialize', $this->_config))
        {
            $this->_auto_serialize = $this->_config['auto_serialize'];
        }
        
        $this->_cache_dir = "{$GLOBALS['midcom_config']['cache_base_directory']}{$this->_config['directory']}";
        $this->_check_cache_dir();
       
        $this->_on_initialize();
        
        debug_pop();
    }

    /**
     * Shutdown the backend. This calls the corresponding event.
     */
    function shutdown()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->_on_shutdown();
        debug_pop();
    }
    
    /**
     * This helper will ensure that the cache base directory is created and usable
     * by checking it is actually a directory. If it does not exist, it will be created
     * automatically. Errors will be handled by calling generate_error.
     * 
     * @access private
     */    
    function _check_cache_dir()
    {
        if (! file_exists($this->_cache_dir))
        {
            if (! @mkdir($this->_cache_dir, 0755))
            {
                // Note: MidCOM is not yet available here
                die("Failed to create the cache base directory {$this->_cache_dir}: {$php_errormsg}");
                // This will exit.
            }
        }
        else if (! is_dir($this->_cache_dir))
        {
            die("Failed to create the cache base directory {$this->_cache_dir}: A file of the same name already exists.");
            // This will exit.
        }
    }
    
    /**#@+
     * Event handler function.
     *
     * @access protected
     */
    
    /**
     * Backend initialization
     * 
     * Add any custom startup code here. The configuration variables are 
     * all initialized when this handler is called.
     */
    function _on_initialize() {}
    
    /**
     * Backend shutdown
     * 
     * Called, if the backend is no longer used.
     */
    function _on_shutdown() {}
    
    /**#@-*/

    /**#@+
     * Internal Data IO API method.
     *
     * @access protected
     */
    
    /**
     * Open the database for usage. If $write is set to true, it must be opened in
     * read/write access, otherwise read-only access is sufficient.
     * 
     * If the database cannot be opened, midcom_application::generate_error() should 
     * be called.
     * 
     * The concrete subclass must track any resource handles internally, of course.
     * 
     * @param boolean $write True, if read/write access is required.
     */
    function _open($write) { die ("The method " . __CLASS__ . "::" . __FUNCTION__ . " must be implemented."); }
    
    /**
     * Close the database that has been opened previously with _open().
     */
    function _close() { die ("The method " . __CLASS__ . "::" . __FUNCTION__ . " must be implemented."); } 
    
    /**
     * Get the data associated with the given key. 
     * 
     * The data store is opened either read-only or read-write when this
     * function executes.
     * 
     * @param string $key Key to look up.
     * @return string $data The data associated with the key.
     */
    function _get($key) { die ("The method " . __CLASS__ . "::" . __FUNCTION__ . " must be implemented."); }
    
    /**
     * Checks, whether the given key exists in the Database.
     * 
     * The data store is opened either read-only or read-write when this
     * function executes.
     * 
     * @param string $key The key to check for.
     * @return boolean Indicating existence.
     */
    function _exists($key) { die ("The method " . __CLASS__ . "::" . __FUNCTION__ . " must be implemented."); }
    
    /**
     * Store the given key/value pair, any existing entry with the same
     * key has to be silently overwritten.
     *
     * The data store is opened in read-write mode when this function executes.
     * 
     * Any error condition should call midcom_application::generate_error() and
     * must close the data store before doing so.
     * 
     * @param string $key The key to store at.
     * @param string $data The data to store.
     */
    function _put($key, $data) { die ("The method " . __CLASS__ . "::" . __FUNCTION__ . " must be implemented."); }
    
    /**
     * Delete the data with the given key from the database. 
     * 
     * The data store is opened in read-write mode when this function executes.
     * 
     * Deleting non existent keys
     * should fail silently. All other error conditions should call 
     * midcom_application::generate_error() and must close the data store before doing so.
     * 
     * @param string $key The key to delete.
     */
    function _remove($key) { die ("The method " . __CLASS__ . "::" . __FUNCTION__ . " must be implemented."); }

    /**
     * Drops the entire database, preferably with some kind of truncate operation.
     * 
     * The data store will not be opened in either read-only or read-write mode when
     * this function executes, to allow for open/truncate operations.
     * 
     * Any error condition should call midcom_application::generate_error().
     */
    function _remove_all() { die ("The method " . __CLASS__ . "::" . __FUNCTION__ . " must be implemented."); }

    /**#@-*/
    
    /**
     * Open the database for usage. If $write is set to true, it must be opened in
     * read/write access, otherwise read-only access is sufficient.
     * 
     * If the database is reopened with different access permissions then currently
     * specified (e.g. if going from read-only to read-write), the database is closed
     * prior to opening it again. If the permissions match the current state, nothing
     * is done.
     * 
     * @param boolean $write True, if read/write access is required.
     */
    function open($write = false)
    {
        // Check, whether the DB is already open.
        if ($this->_open_for_reading)
        {
            // Check whether the access permissions are correct, if yes, we ignore the
            // open request, otherwise we close the db.
            if ($this->_open_for_writing == $write)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("The database has already been opened with the requested permission, ignoring request.");
                debug_pop();
                return;
            }
            
            // Close the db
            $this->_close();
        }
        
        $this->_open($write);
        $this->_open_for_reading = true;
        $this->_open_for_writing = (bool) $write;
    }
    
    /**
     * Close the database that has been opened previously with open(). If the database
     * is already closed, the request is ignored silently.
     */
    function close()
    {
        if (! $this->_open_for_reading)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The database is not open, ignoring the request to close the database.");
            debug_pop();
            return;
        }
        
        $this->_close();
        $this->_open_for_reading = false;
        $this->_open_for_writing = false;
    }
    
    /**
     * Checks, whether the given key exists in the Database. If the data store has not yet
     * been opened for reading, it will be opened automatically prior to the call,
     * and closed automatically again afterwards.
     * 
     * @param string $key The key to check for.
     * @return boolean Indicating existence.
     */
    function exists($key)
    {
        if (! $this->_open_for_reading)
        {
            $auto_close = true;
            $this->open(false);
        }
        else
        {
            $auto_close = false;
        }
        
        $result = $this->_exists($key);
        
        if ($auto_close)
        {
            $this->close();
        }
        
        return $result;
    }
   
    /**
     * Get the data associated with the given key. If the data store has not yet
     * been opened for reading, it will be opened automatically prior to the call,
     * and closed automatically again afterwards.
     * 
     * @param string $key Key to look up.
     * @return string $data The data associated with the key.
     */
    function get($key)
    {
        if (! $this->_open_for_reading)
        {
            $auto_close = true;
            $this->open(false);
        }
        else
        {
            $auto_close = false;
        }
        
        $result = $this->_get($key);
        
        if ($auto_close)
        {
            $this->close();
        }
        
        if (   $this->_auto_serialize
            && is_string($result))
        {
            try
            {
                return unserialize($result);
            }
            catch (Exception $e)
            {
                return;
            }
        }
        else
        {
            return $result;
        }
    }
    
    /**
     * Store the given key/value pair, any existing entry with the same
     * key has to be silently overwritten. If the data store has not yet been 
     * opened for writing, it will be opened automatically prior to the call,
     * and closed automatically again afterwards.
     * 
     * @param string $key The key to store at.
     * @param string $data The data to store.
     */
    function put($key, $data)
    {
        if (! $this->_open_for_writing)
        {
            $auto_close = true;
            $this->open(true);
        }
        else
        {
            $auto_close = false;
        }
        
        if ($this->_auto_serialize)
        {
            $result = $this->_put($key, serialize($data));
        }
        else
        {
            $result = $this->_put($key, $data);
        }
        
        if ($auto_close)
        {
            $this->close();
        }
    }
    
    /**
     * Delete the data with the given key from the database. Deleting non existent keys
     * should fail silently. If the data store has not yet been 
     * opened for writing, it will be opened automatically prior to the call,
     * and closed automatically again afterwards. 
     * 
     * @param string $key The key to delete.
     */
    function remove($key)
    {
        if (! $this->_open_for_writing)
        {
            $auto_close = true;
            $this->open(true);
        }
        else
        {
            $auto_close = false;
        }
        
        $result = $this->_remove($key);
        
        if ($auto_close)
        {
            $this->close();
        }
        
        return $result;

    }
    
    /**
     * Drops the entire database and creates an empty one.
     * 
     * The database must not be opened by this process when this is called. If it is,
     * it will be automatically closed prior to executing this call.
     * 
     * Any error condition should call midcom_application::generate_error().
     */
    function remove_all()
    {
        if ($this->_open_for_reading)
        {
            $this->close();
        }
        $this->_remove_all();
    }
    
}

?>