<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * NULL toolbar
 *
 * @package midcom_core
 */
include MIDCOM_ROOT . "/midcom_core/services/toolbars.php";
class midcom_core_services_toolbars_null implements midcom_core_services_toolbars
{
    /**
     * @param &$configuration Configuration for the current toolbar type
     */
    public function __construct(&$configuration = array())
    {
    }
    
    public function add_item($section, $key, $item)
    {
    }
    
    public function remove_item($section, $key)
    {
    }
    
    public function get_item($section, $key)
    {
    }
    
    /**
     * Returns a reference to the wanted toolbar section of the specified context. The toolbars
     * will be created if this is the first request.
     *
     * @param int $section_id The toolbar block to retrieve, this
     *     defaults to node.
     */
    public function get_section($section)
    {
    }
    
    public function can_view($user = null)
    {
    }
    
    public function render()
    {
    }
}
?>