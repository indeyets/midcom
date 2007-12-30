<?php
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:memcached.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Memcached caching backend.
 *
 * Requires the memcache PECL extension to work, uses persistent connections.
 *
 * <b>Configuration options:</b>
 *
 * - <i>string host</i> The host to connect to, defaults to localhost.
 * - <i>int port</i> The port to connect to, defaults to the default port 11211.
 *
 * <b>Important notes:</b>
 *
 * - This script does not synchronize multiple read/write accesses to the cache in the
 *   sense of a transaction.
 * - This subclass will override the automatic serialization setting you made, as the
 *   memcache extension does this automatically.
 * - Since this is about performance, (and memcached doesn't allow it in any other way),
 *   the get and exist calls are merged. Get will return false in case that the required
 *   key was not found. This effectively means that you cannot store "false" as a value.
 * - The class will automatically add the name of the cache instance to the cache keys.
 *
 * @package midcom.services
 * @link http://www.php.net/manual/en/ref.memcache.php Memcache Documentation
 */

class midcom_services_cache_backend_memcached extends midcom_services_cache_backend
{
    /**
     * The IP to connect to.
     *
     * @access private
     * @var string
     */
    var $_host = 'localhost';

    /**
     * The Port to connect to.
     *
     * @access private
     * @var int
     */
    var $_port = 11211;

    /**
     * The Memcache interface object.
     *
     * @access private
     * @var Memcache
     */
    var $_cache = null;

    /**
     * The constructor is empty yet.
     */
    function midcom_services_cache_backend_memcached()
    {
        parent::midcom_services_cache_backend();
        // Nothing to do.
    }

    /**
     * This handler completes the configuration.
     */
     function _on_initialize()
    {
        if (array_key_exists('host', $this->_config))
        {
            $this->_host = $this->_config['host'];
        }
        if (array_key_exists('port', $this->_config))
        {
            $this->_port = $this->_config['port'];
        }

        // Force-disable the php serializer calls, let memcached worry about it.
        $this->_auto_serialize = false;

        // Open the persistent connection.
        $this->_cache = new Memcache();
        if (! @$this->_cache->pconnect($this->_host, $this->_port))
        {
            die("memcache handler: Failed to connect to {$this->_host}:{$this->_port}.");
            // This will exit.
        }

        /*
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_print_r('Current MemCache stats:', $this->_cache->getStats());
        debug_pop();
        */
    }

    /**
     * This method is unused as we use persistent connections, letting memcached take care about synchronization.
     */
    function _open($write = false) {}

    /**
     * This method is unused as we use persistent connections, letting memcached take care about synchronization.
     */
    function _close() {}


    function _get($key)
    {
        $key = "{$this->_name}-{$key}";
        /* TODO: Remove when done
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Returning \$this->_cache->get('{$key}')");
        debug_pop();
        */
        return (@$this->_cache->get($key));
    }

    function _put($key, $data, $timeout=false)
    {
        $key = "{$this->_name}-{$key}";
        if ($timeout !== FALSE)
        {
            /* TODO: Remove when done
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Returning \$this->_cache->set('{$key}', \$data, 0, {$timeout})");
            debug_pop();
            */
            @$this->_cache->set($key, $data, 0, $timeout);
            return;
        }
        /* TODO: Remove when done
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Returning \$this->_cache->set('{$key}', \$data)");
        debug_pop();
        */
        @$this->_cache->set($key, $data);
    }

    function _remove($key)
    {
        $key = "{$this->_name}-{$key}";
        /* TODO: Remove when done
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Returning \$this->_cache->delete('{$key}')");
        debug_pop();
        */
        @$this->_cache->delete($key);
    }

    function _remove_all()
    {
        @$this->_cache->flush();
    }

    /**
     * Exists maps to the getter function, as memcached does not support exists checks.
     */
    function _exists($key)
    {
        /* TODO: Remove when done
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Returning (@\$this->_get('{$key}') !== false)");
        debug_pop();
        */
        return (@$this->_get($key) !== false);
    }

}
?>