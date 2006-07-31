<?php
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:dba.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * DBA-Style flat file database caching backend.
 * 
 * Uses DBA database locking for synchronization.
 * 
 * <b>Confiugration options:</b>
 * 
 * - <i>string handler</i> Defines the DBA handler to use. If omitted, autodetection
 *   is attempted.
 * 
 * @package midcom.services
 */

class midcom_services_cache_backend_dba extends midcom_services_cache_backend
{
    /**
     * The handler to use
     * 
     * @access private
     * @var string
     */
    var $_handler = null;
    
    /**
     * The full database filename.
     * 
     * @access private
     * @var string
     */
    var $_filename = null;
    
    /**
     * The current handle, controlled by _open() and _close().
     * 
     * @access private
     * @var resource
     */
    var $_handle = null;
    
    
    /**
     * The constructor is empty yet.
     */
    function midcom_services_cache_backend_dba()
    {
        parent::midcom_services_cache_backend();
        // Nothing to do.
    }
    
    /**
     * This handler completes the configuration.
     */
 	function _on_initialize()
    {
        if (array_key_exists('handler', $this->_config))
        {
            $this->_handler = $this->_config['handler'];
        }
        else
        {
            if (function_exists('dba_handlers'))
            {
                $handlers = dba_handlers();
                if (in_array('db4', $handlers))
                {
                    $this->_handler = 'db4';
                }
                else if (in_array('db3', $handlers))
                {
                    $this->_handler = 'db3';
                }
                else if (in_array('db2', $handlers))
                {    
                    $this->_handler = 'db2';
                }
                else if (in_array('gdbm', $handlers))
                {
                    $this->_handler = 'gdbm';
                }
                else if (in_array('flatfile', $handlers))
                {
                    $this->_handler = 'flatfile';
                }
                else
                {
                    // TODO: No MidCOM here
                    $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                    "dba cache handler: Failed autodetection of a usable DBA handler. Found handlers were: {$handlers}"); 
                    // This will exit.
                }
            }
            else
            {
                // TODO: No MidCOM here
	            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                    "dba cache handler autodetection: DBA-Functions unavailable or PHP prior to 4.3 is being used (checked for the function dba_handlers)."); 
                // This will exit.
            }
        }
        $this->_filename = "{$this->_cache_dir}{$this->_name}.{$this->_handler}";
        
        // Check for file existance by opening it once for write access.
        if (! file_exists($this->_filename))
        {
            $handle = dba_open($this->_filename, 'c', $this->_handler);
            if ($handle === false)
            {
                $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                    "dba cache handler: Failed to open the database {$this->_filename} for creation.");
                // This will exit.
            }
            dba_close($handle);
        }
        
        debug_add("DBA Cache backend '{$this->_name}' initialized to file: {$this->_filename}");
    }
    
    /**
     * Internal helper, which opens a handle to the DBA file in either
     * read-only or read/write mode. The handle has to be closed by the
     * _close() function.
     * 
     * The handle is stored in $_handle
     * 
     * @param bool $write Set to true to enable read/write access with the corresponding exclusive lock. Otherwise
     * 	   shared read-only mode is used.
     */
    function _open($write = false)
    {
        if ($write)
        {
            $handle = @dba_open($this->_filename, 'w', $this->_handler);
        }
        else
        {
            $handle = @dba_open($this->_filename, 'r', $this->_handler);
        }
        if ($handle === false)
        {
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                "dba cache handler: Failed to open the database {$this->_filename} (Write-mode: {$write}): {$php_errormsg}");
            // This will exit.
        }
        $this->_handle = $handle;
    }
    
    function _close()
    {
        if ($this->_handle == null)
        {
        	debug_add("There was a NULL handle in the DBA backend, ignoring close request.");
            return;
        }
        dba_close($this->_handle);
        $this->_handle = null;
    }
    
    
    function _get($key)
    {
        if (! $this->_exists($key))
        {
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                "dba cache handler: Failed to read key {$key} from the database {$this->_filename}: The key does not exist.");
            // This will exit.
        }
        $result = @dba_fetch($key, $this->_handle);
        if ($result === false)
        {
            // Note: Appearantly not php error here, so no php_errormsg as well.
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                "dba cache handler: Failed to read key {$key} from the database {$this->_filename}: {$php_errormsg}");
            // This will exit.
        }
        return $result;
    }
    
    function _put($key, $data)
    {
        if (! @dba_replace($key, $data, $this->_handle))
        {
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                "dba cache handler: Failed to write key {$key} to the database {$this->_filename}: {$php_errormsg}");
            // This will exit.
        }
    }
    
    function _remove($key)
    {
        if (! $this->_exists($key))
        {
            debug_add("The key {$key} does not exist, so not removing it.");
            return;
        }
        if (! @dba_delete($key, $this->_handle))
        {
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                "dba cache handler: Failed to remove key {$key} from the database {$this->_filename}: {$php_errormsg}");
            // This will exit.
        }
    }
    
    function _remove_all()
    {
    	// This will open the database in truncate/write mode once to clear the file.
    	$handle = @dba_open($this->_filename, 'n', $this->_handler);
        if ($handle === false)
        {
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                "dba cache handler: Failed to truncate the database {$this->_filename}: {$php_errormsg}");
            // This will exit.
        }
        dba_close($handle);
    }
    
    function _exists($key)
    {
        return dba_exists($key, $this->_handle);
    }
    
}
