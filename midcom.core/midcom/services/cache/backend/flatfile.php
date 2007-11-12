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
        $this->_dirname = "{$this->_cache_dir}{$this->_name}/";
        
        // Check for file existance.
        if (! file_exists($this->_dirname))
        {
            mkdir($this->_dirname);
        }
        
        $this->_auto_serialize = true;
        
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
     
    function _get($key)
    {
        if (! $this->exists($key))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("flatfile cache handler: Failed to read key {$key} from the database {$this->_dirname}: File does not exist.", MIDCOM_LOG_ERROR);
            debug_pop();
            return null;
        }
        return file_get_contents("{$this->_dirname}{$key}");
    }
    
    function _put($key, $data)
    {
        $filename = "{$this->_dirname}{$key}";
        if (file_exists($filename))
        {
            unlink($filename);
        } 
        $handle = @fopen($filename, 'x');
        if ($handle === false)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("flatfile cache handler: Failed to create key {$key} in the database {$this->_dirname}: File does already exist.", MIDCOM_LOG_ERROR);
            debug_pop();
            return;
        }
        fwrite($handle, $data);
        fclose($handle);
    }
    
    function _remove($key)
    {
        @unlink("{$this->_dirname}{$key}");
    }
    
    function _remove_all()
    {
        // This will rename the current directory, create a new empty one and 
        // then completely delete the original directory.
        $tmpdir = substr($this->_dirname, 0, strlen($this->_dirname) - 1) . '.' . getmypid();
        rename($this->_dirname, $tmpdir);
        mkdir($this->_dirname);
        
        // Wait a bit (0.1 sec) in case there are still files open.
        usleep(100000);
        $tmp_directory = dir($tmpdir);
        while (false !== ($entry = $tmp_directory->read())) 
        {
            if (substr($entry, 0, 1) == '.')
            {
                // Ignore dotfiles
                continue;
            }
            
            $filename = "{$tmpdir}/{$entry}";
            if (!@unlink($filename))
            {
                debug_add( "Could not clear flatfile cache {$filename}. Most probably due to missing permissions.");
            } 
        }
        $tmp_directory->close();

        if (!@rmdir($tmpdir))
        {
            // Perhaps there is a file left?
            debug_add("Failed to delete {$tmpdir} during cache invalidation. Please delete it manually.", MIDCOM_LOG_CRIT); 
        }
    }
    
    function _exists($key)
    {
        return file_exists("{$this->_dirname}{$key}");
    }
    
}
