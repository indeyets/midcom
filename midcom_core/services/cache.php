<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Cache interface for MidCOM 3
 *
 * @package midcom_core
 */
interface midcom_core_services_cache
{
    /**
     * Function gets a record from cache by key
     *
     * @param cache_id $key
     */
    public function get($key);
    
    /**
     * Function gets records from cache that have been tagged 
     *
     * @param string or Array $tags
     */
    public function get_by_tag($tags);
    
    /**
     * Function puts a record to cache
     *
     * @param string $key
     * @param Serializable $data
     * @param int $timeout
     * @param String / Array $tag
     */
    public function put($key, $data, $timeout = false, $tags=null);
    
    /**
     * Removes an entry from cache
     *
     * @param string $key
     */
    public function remove($key);
    
    /**
     * Removes entries from cache by tag
     *
     * @param string / Array $tag
     */
    public function remove_by_tag($tags);
    
    /**
     * Removes all entries from cache
     *
     */
    public function remove_all();
    
    /**
     * Checks if a record exists in cache
     *
     * @param string $key
     */
    public function exists($key);
}
?>