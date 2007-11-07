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
     * The constructor is empty yet.
     */
  function midcom_services_cache_backend_sqlite()
  {
    parent::midcom_services_cache_backend();
    $this->_db = new SQLiteDatabase($this->_cache_dir.'/content_cache'); // Opening database connection
    // Nothing to do.
  }

  /**
     * This handler completes the configuration.
     */
  function _on_initialize()
  {
    /**
       * Checking if table exists in cache-database
       */
    $result = $this->_db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$this->_table'");
    if(count($result->fetchAll) == 0)
    {
      $this->_db->query("CREATE TABLE {$this->_table} (key VARCHAR(255), value TEXT);");
    }
  }

  function get($key)
  {
    $key = sqlite_escape_string($key);
    $results = $this->_db->query("SELECT value FROM $this->_table WHERE key='$key'");
    $results = $results->fetchAll();
    if(count($results) == 0){
      return false; // No hit.
    }
    else{
      return $results[0]['value'];
    }
  }

  function put($key, $data)
  {
    $key = sqlite_escape_string($key);
    $data = sqlite_escape_string($data);
    $this->_db->query("REPLACE INTO $this->_name (key, value) VALUES ('$key', '$data')");
  }

  function remove($key)
  {
    $key = sqlite_escape_string($key);
    $this->_db->query("DELETE FROM $this->_name WHERE key='$key'");
  }

  function remove_all()
  {
    $this->_db->query("DELETE FROM $this->_name WHERE 1");
  }

  function exists($key)
  {
    $key = sqlite_escape_string($key);
    $results = $this->_db->query("SELECT value FROM $this->_table WHERE key='$key'");
    $results = $results->fetchAll();
    if(count($results) == 0){
      return false; // No hit.
    }
    else
    {
      return true; // Hit
    }
  }
}
