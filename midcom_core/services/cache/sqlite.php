<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

include MIDCOM_ROOT . "/midcom_core/services/cache.php";

/**
 * SQLite cache backend.
 * 
 * Backend requires SQLite3 PECL package for PHP
 *
 * @package midcom_core
 */
class midcom_core_services_cache_sqlite extends midcom_core_services_cache_base implements midcom_core_services_cache
{
    private $_db;
    private $_table;
    
    public function __construct()
    {
        $this->_db = new SQLite3("{$_MIDCOM->configuration->cache_directory}/{$_MIDCOM->configuration->cache_name}.sqlite");
        
        $this->_table = str_replace(array(
            '.', '-'
        ), '_', $_MIDCOM->configuration->cache_name);
        
        // Check if we have a DB table corresponding to current cache name 
        $result = $this->_db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$this->_table}'");
        $tables = $result->fetchArray();
        if (count($tables) == 0 || $tables == false)
        {
            /**
             * Creating table for data
             */
            $this->_db->query("CREATE TABLE {$this->_table} (module VARCHAR(255), identifier VARCHAR(255), value TEXT);");
            $this->_db->query("CREATE INDEX {$this->_table}_identifier ON {$this->_table} (identifier);");
            $this->_db->query("CREATE INDEX {$this->_table}_module ON {$this->_table} (module);");
            
            /**
             * Creating table for tags
             */
            $this->_db->query("CREATE TABLE {$this->_table}_tags (identifier VARCHAR(255), tag VARCHAR(255));");
            $this->_db->query("CREATE INDEX {$this->_table}_tags_i ON {$this->_table}_tags (identifier, tag);");
        }
    }

    public function register($identifier, array $tags)
    {
        $identifier = SQLite3::escapeString($identifier);
        foreach ($tags as $tag)
        {
            $tag = SQLite3::escapeString($tag);
            $this->_db->query("REPLACE INTO {$this->_table}_tags (tag, identifier) VALUES ('{$tag}', '{$identifier}')");
        }
    }
    
    public function invalidate(array $tags)
    {
        foreach ($tags as $tag)
        {
            $tag = SQLite3::escapeString($tag);
            $results = $this->_db->query("SELECT identifier FROM {$this->_table}_tags WHERE tag='{$tag}'");
            while ($row = $results->fetchArray())
            {
                $this->_db->query("DELETE FROM {$this->_table} WHERE identifier='{$row[0]}'");
                $this->_db->query("DELETE FROM {$this->_table}_tags WHERE identifier='{$row[0]}'");

            }
        }
    }

    public function invalidate_all()
    {
        $this->_db->query("DELETE FROM {$this->_table} WHERE 1");
        $this->_db->query("DELETE FROM {$this->_table}_tags WHERE 1");
    }
    
    public function put($module, $identifier, $data)
    {
        $module = SQLite3::escapeString($module);
        $identifier = SQLite3::escapeString($identifier);
        $data = SQLite3::escapeString(serialize($data));
        return $this->_db->query("REPLACE INTO {$this->_table} (module, identifier, value) VALUES ('{$module}', '{$identifier}', '{$data}')");
    }
    
    public function get($module, $identifier)
    {
        $module = SQLite3::escapeString($module);
        $identifier = SQLite3::escapeString($identifier);
        $results = $this->_db->query("SELECT value FROM {$this->_table} WHERE module='{$module}' AND identifier='{$identifier}'");
        $results = $results->fetchArray();
        
        if (count($results) == 0)
        {
            return false; // no hit
        }
        
        return unserialize($results[0]);
    }       
    
    public function delete($module, $identifier)
    {
        $key = SQLite3::escapeString($identifier);
        $module = SQLite3::escapeString($module);
        $this->_db->query("DELETE FROM {$this->_table} WHERE module='{$module}' AND identifier='{$identifier}'");
        $this->_db->query("DELETE FROM {$this->_table}_tags WHERE identifier='{$identifier}'");
    }
    
    public function exists($module, $identifier)
    {
        if( $this->get($module, $identifier) == false)
        {
            return false;
        }
        return true;
    }
    
    
}
?>