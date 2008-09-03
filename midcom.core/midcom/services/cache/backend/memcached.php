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
 * <b>Confiugration options:</b>
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
 *   key was not found. This effectivily means that you cannot store "false" as a value.
 * - The class will automatically add the name of the cache instance to the cache keys.
 *
 * @package midcom.services
 * @see http://www.php.net/manual/en/ref.memcache.php
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
     * Whether to abort the request if connection to memcached fails
     */
    var $_abort_on_fail = false;
    
    /**
     * Whether memcached is working
     */
    var $_memcache_operational = true;

    /**
     * The constructor is empty yet.
     */
    function midcom_services_cache_backend_memcached()
    {
        parent::__construct();
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

        // Open the persistant connection.
        $this->_cache = new Memcache();
        if (! @$this->_cache->pconnect($this->_host, $this->_port))
        {
            // Memcache connection failed
            if ($this->_abort_on_fail)
            {
                // Abort the request
                /**
                 * We don't have the superglobal initialized yet
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "memcache handler: Failed to connect to {$this->_host}:{$this->_port}.");
                 */
                header('HTTP/1.0 503 Service Unavailable');
                header('Retry-After: 60');
                header('Cache-Control: no-store, no-cache, must-revalidate');
                header('Cache-Control: post-check=0, pre-check=0', false);
                header('Pragma: no-cache');
                die("memcache handler: Failed to connect to {$this->_host}:{$this->_port}.");
                // This will exit.
            }
            
            // Otherwise we just skip caching
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("memcache handler: Failed to connect to {$this->_host}:{$this->_port}. Serving this request without cache.", MIDCOM_LOG_ERROR);
            debug_pop();
            $this->_memcache_operational = false;
        }

        /*
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_print_r('Current MemCache stats:', $this->_cache->getStats());
        debug_pop();
        */
    }

    /**
     * This method is unused as we use persistant connections, letting memcached take care about synchronization.
     */
    function _open($write = false) {}

    /**
     * This method is unused as we use persistant connections, letting memcached take care about synchronization.
     */
    function _close() {}


    function _get($key)
    {
        if (!$this->_memcache_operational)
        {
            return;
        }
        
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
        if (!$this->_memcache_operational)
        {
            return;
        }
        
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
        if (!$this->_memcache_operational)
        {
            return;
        }
        
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
        if (!$this->_memcache_operational)
        {
            return;
        }
        
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Calling \$this->_cache->flush()");
        $stat = @$this->_cache->flush();
        debug_add("\$this->_cache->flush() returnder with " . (int)$stat);
        debug_pop();
    }

    /**
     * Exists maps to the getter function, as memcached does not support exists checks.
     */
    function _exists($key)
    {
        if (!$this->_memcache_operational)
        {
            return false;
        }
        
        /* TODO: Remove when done
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Returning (@\$this->_get('{$key}') !== false)");
        debug_pop();
        */
        return (@$this->_get($key) !== false);
    }

}
