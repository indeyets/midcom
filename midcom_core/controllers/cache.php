<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM cache management controller
 *
 * @package midcom_core
 */
class midcom_core_controllers_cache
{
    public function __construct($instance)
    {
        $this->configuration = $_MIDCOM->configuration;
    }
    
    public function action_invalidate($route_id, &$data, $args)
    {
        $_MIDCOM->authorization->require_user();
        $_MIDCOM->cache->invalidate_all();
        header('Location: ' . $_MIDCOM->dispatcher->generate_url('page_show', array()));
        exit();
    }
}
?>