<?php
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:phpscripts.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This cache module is desiged to cache files generated by various MidCOM code
 * generation system. This includes:
 * 
 * - DBA Intermediate classes (Namespace midcom.dba)
 * - Component Manifest (Namespace midcom.componentloader)
 * 
 * The cached scripts are invalidated based on their timestamp. Whenever you load a
 * script from the cache, you have to pass a last modified timestamp to it. This 
 * stamp is compared with the modification date of the script file, leading to
 * automatic invalidation in case of outdated script files.
 * 
 * Each cached script must have an identifier, which must be namespaced according to
 * the MidCOM namespacing conventions. Identifiers may consist of any valid filename
 * character, a .php extension is appended automatically on all operations, you should
 * not do that yourself. Namespace and local identifier should be separated by a dash.
 * While adding, you do not need to add the PHP opening / closing tags to the source 
 * code, it will be done automatically during cache file creation.
 * 
 * Both the load and add operations will automatically require_once() the script file
 * after successful completion. 
 * 
 * <i>Danger, Will Robinson:</i>
 * 
 * You should be aware, that the module in itself has no separation of sites whatsoever,
 * as these scripts are usually valid for an entire installation. In case your application
 * needs to cache scripts per-site or per-content-tree, you need to ensure the uniqueness
 * of the cache identifiers yourself, for example by adding the corresponding GUIDs.
 * 
 * @package midcom.services
 */
class midcom_services_cache_module_phpscripts extends midcom_services_cache_module
{
    
    /**
     * The base directory in which we may add script files.
     * 
     * @var string
     * @access private
     */
    var $_cache_dir;
    
    /**
     * Initializes the cache module, verifying the existence of the script cache 
     * directory.
     */
    function _on_initialize()
    {
    	$this->_cache_dir = $GLOBALS['midcom_config']['cache_base_directory']
            . $GLOBALS['midcom_config']['cache_module_phpscripts_directory'];
        
        if (! file_exists($this->_cache_dir))
        {
            if (! @mkdir($this->_cache_dir, 0755))
            {
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
    
    /**
     * Checks the cache entry against the passed last modification timestamp. If it is still
     * valid, the corresponding file is loaded by using require_once(). Otherwise, the cache
     * copy is deleted and false will be returned, you have to create the cache entry using 
     * the add() call then.
     * 
     * This class supports a variable length argument list: You may add more then one last
     * modified timestamp to the call, in which case only the newest timestamp is taken into
     * account.
     * 
     * @param string $identifier The script cache identifier to load (without the trailing .php).
     * @param int $lastmodified The last modification date of the source on which the cached file
     *     is based upon. If you pass more then one timestamp, the newest timestamp is used for
     *     the comparison.
     * @return bool Indicating success.
     */
    function load($identifier, $lastmodified)
    {
        $filename = "{$this->_cache_dir}{$identifier}.php";
        if (! file_exists($filename))
        {
            return false;
        }
        
        if (func_num_args() > 2)
        {
            $timestamps = func_get_args();
            // Skip the identifier
            array_shift($timestamps);
            $compare_stamp = max($timestamps);
        }
        else
        {
            $compare_stamp = $lastmodified;
        }
        
        $file_stamp = filemtime($filename);
        
        $compare_stamp_clear = strftime("%x %X", $compare_stamp);
        $file_stamp_clear = strftime("%x %X", $file_stamp);
        
        if ($compare_stamp > $file_stamp)
        {
            @unlink($filename);
            return false;
        }
        
        // Execute.
        require_once($filename);
        
        return true;
    }
    
    /**
     * This call adds a script to the code file cache. Any existing file will be truncated before
     * the new file is written. 
     * 
     * You do not need to add the surrounding php opening / closing tags to the script code, this
     * happens automatically. If the script file has been written out successfully, it will be
     * require'd from there immediately. If writing of the cache file fails, an error is logged,
     * and <i>the script is not executed</i>. If you want a silent fallback in this case, eval
     * the code yourself if false is returned.
     * 
     * @param string $identifier The script cache identifier to load (without the trailing .php).
     * @param string $code The code to add to the cache, it must <i>not</i> include the php 
     *     opening/closing tags, they will be added automatically during cache file creation.
     * @param bool Indicating success.
     */
    function add($identifier, $code)
    {
        $filename = "{$this->_cache_dir}{$identifier}.php";
        
        $handle = @fopen($filename, 'w'); 
        if (! $handle)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to open the file {$filename} for writing.", MIDCOM_LOG_ERROR);
            if (is_set($php_errormsg))
            {
            	debug_add($php_errormsg, MIDCOM_LOG_ERROR);
            }
            debug_pop();
            return false;
        }
        
        if (! @fwrite($handle, "<?php\n{$code}\n?>\n"))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to write to the file {$filename}.", MIDCOM_LOG_ERROR);
            if (isset($php_errormsg))
            {
            	debug_add($php_errormsg, MIDCOM_LOG_ERROR);
            }
            fclose($handle);
            debug_pop();
            return false;
        }
        
        fclose($handle);
        
        require_once($filename);
        
        return true;
    }
    
    /**
     * This function creates a filename out of the given namespace and local identifier pair.
     * Be aware, that this script does <i>not</i> protect against misuse of the system, it 
     * is limited to prevent accidential problems like slashes in path names and the like.
     * Namespace and local identifier are separated by a dash.
     * 
     * Any invalid characters are replaced with underscores.
     * 
     * @param string $namespace The namespace to use.
     * @param string $local_identifier The local identifier to use.
     * @return string The full cache file identifier or false on failure. 
     */
    function create_identifier($namespace, $local_identifier)
    {
        if($namespace == '')
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_log('The identifier must not be empty.', MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }
        $identifier = "{$namespace}-{$local_identifier}";
        $identifier = str_replace
        (
            Array
            (
                '/'
            ),
            Array
            (
                '_'
            ),
            $identifier
        );
        return $identifier;
    }
    
    /**
     * The GUID invalidation wrapper is empty, as cached script files are not bound to any GUID.
     */
    function invalidate($guid)
    {
        return; 
    }
    
    /**
     * The invalidate_all call will just remove all .php files from the defined cache directory.
     */
    function invalidate_all()
    {
        $files = glob($this->_cache_dir . "*.php",GLOB_NOSORT);
        foreach ($files as $file)
        {
            if (!unlink($file))
            {
                echo "Could not clear phpfilecache. Most probably due to missing permissions.";
            }
        }
    }
}

?>
