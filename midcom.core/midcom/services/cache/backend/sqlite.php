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
 * 
 * @todo Implement proper locking
 * @package midcom.services
 */

class midcom_services_cache_backend_sqlite extends midcom_services_cache_backend
{
    /**
     * The full directory filename.
     * 
     * @access private
     * @var string
     */
    var $_db = null;

    /**
     * Sanitized version of database table name
     */
    var $_table = '';

    /**
     * The constructor is empty yet.
     */
    function __construct()
    {
        parent::__construct();
        // Nothing to do.
    }

    /**
     * This handler completes the configuration.
     */
    function _on_initialize()
    {
        // We need to serialize data
        $this->_auto_serialize = true;

        // Opening database connection
        $this->_db = new SQLiteDatabase("{$this->_cache_dir}/{$this->_name}.sqlite");
        
        $this->_table = str_replace(array('.', '-'), '_', $this->_name);
        
        // Check if we have a DB table corresponding to current cache name        
        $result = $this->_db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$this->_table}'");
        $tables = $result->fetchAll();
        if (count($tables) == 0)
        {
            $this->_db->query("CREATE TABLE {$this->_table} (key VARCHAR(255), value TEXT);");
            $this->_db->query("CREATE INDEX {$this->_table}_key ON {$this->_table} (key);");
        }
    }
    
    function _open($write) {}

    function _close() {}

    function _get($key)
    {
        /* TODO: Remove when done
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("key '{$key}')");
        debug_pop();
        */
        $key = sqlite_escape_string($key);
        $results = $this->_db->query("SELECT value FROM {$this->_table} WHERE key='{$key}'");
        $results = $results->fetchAll();
        if(count($results) == 0)
        {
          return false; // No hit.
        }
        
        return $results[0]['value'];
    }

    function _put($key, $data)
    {
        /* TODO: Remove when done
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("key '{$key}')");
        debug_pop();
        */
        $key = sqlite_escape_string($key);
        $data = sqlite_escape_string($data);
        $this->_db->query("REPLACE INTO {$this->_table} (key, value) VALUES ('{$key}', '{$data}')");
    }

    function _remove($key)
    {
        /* TODO: Remove when done
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("key '{$key}')");
        debug_pop();
        */
        $key = sqlite_escape_string($key);
        $this->_db->query("DELETE FROM {$this->_table} WHERE key='{$key}'");
    }

    function _remove_all()
    {
        $this->_db->query("DELETE FROM $this->_table WHERE 1");
    }

    function _exists($key)
    {
        /* TODO: Remove when done
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("key '{$key}')");
        debug_pop();
        */
        $key = sqlite_escape_string($key);
        $results = $this->_db->query("SELECT count(*) AS exists FROM {$this->_table} WHERE key=\"{$key}\"");

        $results = $results->fetchAll();
        if (empty($results))
        {
            return false;
        }
        
        if ($results[0]['exists'] == 0)
        {
            return false; // No hit.
        }
        
        return true;
    }
}

?>