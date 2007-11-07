<?php
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:flatfile.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Simple flat file database backend. Creates a file per key.
 * 
 * No locking is done within this backend yet.
 * 
 * <b>Confiugration options:</b>
 *
 * None
 * 
 * @todo Implement proper locking
 * @package midcom.services
 */

class midcom_services_cache_backend_flatfile extends midcom_services_cache_backend
{
    /**
     * The full directory filename.
     * 
     * @access private
     * @var string
     */
    var $_dirname = null;
    
    /**
     * The constructor is empty yet.
     */
    function midcom_services_cache_backend_flatfile()
    {
        parent::midcom_services_cache_backend();
        // Nothing to do.
    }
    
    /**
     * This handler completes the configuration.
     */
 	function _on_initialize()
    {
        $this->_dirname = "{$this->_cache_dir}{$this->_name}";
        
        // Check for file existance.
        if (! file_exists($this->_dirname))
        {
            mkdir($this->_dirname);
        }
        
        //debug_add("Flatfile Cache backend '{$this->_name}' initialized to directory: {$this->_dirname}");
    }

    /**
     * This method is unused as we use flat files that are accessed per key
     */
    function _open($write = false) {}

    /**
     * This method is unused as we use flat files that are accessed per key
     */
    function _close() {}
     
    function get($key)
    {
        if (! $this->exists($key))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "flatfile cache handler: Failed to read key {$key} from the database {$this->_dirname}: File does not exist.");
            // This will exit.
        }
        return file_get_contents("{$this->_dirname}{$key}");
    }
    
    function put($key, $data)
    {
        $filename = "{$this->_dirname}{$key}";
        if (file_exists($filename))
        {
            unlink($filename);
        } 
        $handle = @fopen($filename, 'x');
        if ($handle === false)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "flatfile cache handler: Failed to create key {$key} in the database {$this->_dirname}: File does already exist.");
            // This will exit.
        }
        fwrite($handle, $data);
        fclose($handle);
    }
    
    function remove($key)
    {
        @unlink("{$this->_dirname}{$key}");
    }
    
    function remove_all()
    {
        // This will rename the current directory, create a new empty one and 
        // then completely delete the original directory.
        $tmpdir = "{$this->_dirname}." . getmypid();
        rename($this->_dirname, $tmpdir);
        mkdir($this->_dirname);
        
        // Wait a bit (0.1 sec) in case there are still files open.
        usleep(100000);
        $files = glob($tmpdir . "*",GLOB_NOSORT);
        foreach ($files as $file)
        {
            if (!unlink($file))
            {
                debug_add( "Could not clear phpfilecache. Most probably due to missing permissions.");
            }
        }


        if (!@rmdir($tmpdir))
        {
            // Perhaps there is a file left?
            debug_add("Failed to delete {$tmpdir} during cache invalidation. Please delete it manually.", MIDCOM_LOG_CRIT); 
        }
    }
    
    function exists($key)
    {
        return file_exists("{$this->_dirname}{$key}");
    }
    
}
