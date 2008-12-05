<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * SQLite cache backend.
 *
 * @package midcom_core
 */
class midcom_core_services_cache_sqlite implements midcom_core_services_cache
{
    private $_db;
    private $_table;
    
    public function __construct()
    {
        $this->_db = new SQLiteDatabase("{$_MIDCOM->configuration->cache_directory}/{$_MIDCOM->configuration->cache_name}.sqlite");
        $this->_table = str_replace(array(
            '.', '-'
        ), '_', $this->_name);
        
        // Check if we have a DB table corresponding to current cache name 
        $result = $this->_db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$this->_table}'");
        $tables = $result->fetchAll();
        if (count($tables) == 0)
        {
            /**
             * Creating table for data
             */
            $this->_db->query("CREATE TABLE {$this->_table} (key VARCHAR(255), value TEXT);");
            $this->_db->query("CREATE INDEX {$this->_table}_key ON {$this->_table} (key);");
            
            /**
             * Creating table for tags
             */
            $this->_db->query("CREATE TABLE {$this->_table}_tags (key VARCHAR(255), tag VARCHAR(255));");
            $this->_db->query("CREATE INDEX {$this->_table}_tags ON {$this->_table}_tags (key, tag);");
        }
    }
    
    public function get($key)
    {
        $key = sqlite_escape_string($key);
        $results = $this->_db->query("SELECT value FROM {$this->_table} WHERE key='{$key}'");
        $results = $results->fetchAll();
        if (count($results) == 0)
        {
            return false; // no hit
        }
        
        return $results[0]['value'];
    }
    
    public function get_by_tag($tags)
    {
        $constraint = '';
        if (is_array($tags))
        {
            foreach ($tags as $tag)
            {
                $tag = sqlite_escape_string($tag);
                $constraint .= "{$this->_table}_tags.tag='{$tag}' OR ";
            }
            $constraint = substr($constraint, 0, strlen($constraint) - 3);
        }
        else
        {
            $tags = sqlite_escape_string($tags);
            $constraint = "{$this->_table}_tags.tag='{$tag}'";
        }
        // Making a query
        $query = ("SELECT {$this->_table}.key AS key, {$this->_table}.value AS value FROM {$this->_table}
        LEFT JOIN {$this->_table}_tags ON {$this->_table}_tags.key={$this->_table}.key
        WHERE $constraint
        ");
        
        $results = $this->_db->query($query);
        $results = $results->fetchAll();
        if (count($results) == 0)
        {
            return false; // no hit
        }
        
        return $results;
    }
    
    public function put($key, $data, $timeout = false, $tags = null)
    {
        $key = sqlite_escape_string($key);
        $data = sqlite_escape_string($data);
        $this->_db->query("REPLACE INTO {$this->_table} (key, value) VALUES ('{$key}', '{$data}')");
        if (! is_null($tags))
        {
            if (is_array($tags))
            {
                foreach ($tags as $tag)
                {
                    $tag = sqlite_escape_string($tag);
                    $tag_id = $this->checktag($tag);
                    $this->_db->query("REPLACE INTO {$this->_table}_tags (tag, key) VALUES ('{$tag}', '{$key}')");
                }
            }
            else
            {
                $tags = sqlite_escape_string($tags);
                $tag_id = $this->checktag($tags);
                $this->_db->query("REPLACE INTO {$this->_table}_tags (tag, key) VALUES ('{$tag}', '{$key}')");
            }
        }
    }
    
    public function remove($key)
    {
        $key = sqlite_escape_string($key);
        $this->_db->query("DELETE FROM {$this->_table} WHERE key='{$key}'");
        $this->_db->query("DELETE FROM {$this->_table}_tags WHERE key='{$key}'");
    }
    
    public function remove_by_tags($tags)
    {
        if (is_array($tags))
        {
            foreach ($tags as $tag)
            {
                $tag = sqlite_escape_string($tag);
                $results = $this->_db->query("SELECT key FROM {$this->_table}_tags WHERE tag='{$tag}'");
                $results = $results->fetchAll();
                foreach ($results as $r)
                {
                    $this->_db->query("DELETE FROM {$this->_table} WHERE key='{$r['key']}");
                    $this->_db->query("DELETE FROM {$this->_table}_tags WHERE key='{$r['key']}'");
                }
            }
        }
        else
        {
            $tags = sqlite_escape_string($tags);
            $results = $this->_db->query("SELECT key FROM {$this->_table}_tags WHERE tag='{$tags}'");
            $results = $results->fetchAll();
            foreach ($results as $r)
            {
                $this->_db->query("DELETE FROM {$this->_table} WHERE key='{$r['key']}");
                $this->_db->query("DELETE FROM {$this->_table}_tags WHERE key='{$r['key']}'");
            }
        }
    }

    public function remove_all()
    {
        $this->_db->query("DELETE FROM {$this->_table} WHERE 1");
        $this->_db->query("DELETE FROM {$this->_table}_tags WHERE 1");
    }
    
    public function exists($key)
    {
        if($this->get($key) == false)
        {
            return false;
        }
        return true;
    }
    
    
}
?>