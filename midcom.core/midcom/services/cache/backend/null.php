<?php
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:flatfile.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Database backend that does not do anything.
 * @package midcom.services
 */
class midcom_services_cache_backend_null extends midcom_services_cache_backend
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
        return;
    }

    function _check_cache_dir()
    {
        return;
    }

    function _open($write = false) {}

    function _close() {}

    function get($key)
    {
        return null;
    }
    
    function put($key, $data)
    {
       return;
    }
    
    function remove($key)
    {
        return;
    }
    
    function remove_all()
    {
        return;
    }
    
    function exists($key)
    {
        return false;
    }
    
}
