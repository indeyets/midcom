<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

include MIDCOM_ROOT . "/midcom_core/services/cache.php";

/**
 * Midgard cache backend.
 *
 * This cache backend stores cached data to host's parameter
 * Primary use for the backend is for testing and developing purposes
 *
 * @package midcom_core
 */
class midcom_core_services_cache_midgard extends midcom_core_services_cache_base implements midcom_core_services_cache
{
    private $_db;
    private $_table;
    
    public function __construct()
    {
    }
    public function put($module, $identifier, $data)
    {
        $_MIDCOM->context->host->set_parameter("midcom_core_services_cache_midgard:{$module}", $identifier, $data);
    }
    public function get($module, $identifier)
    {
        return $_MIDCOM->context->host->get_parameter("midcom_core_services_cache_midgard:{$module}", $identifier);
    }
    public function delete($module, $identifier)
    {
        $args = array(  'domain' => "midcom_core_services_cache_midgard:{$module}",
                        'name' => $identifier);
        $_MIDCOM->context->host->delete_parameters($args);        
    }
    public function exists($module, $identifier)
    {
        if (is_null ($this->get($module, $identifier)))
        {
            return false;
        }
        return true;
    }
    public function delete_all($module)
    {
        $args = array('domain' => "midcom_core_services_cache_midgard:{$module}");
        $_MIDCOM->context->host->delete_parameters($args)
    }    
}
?>