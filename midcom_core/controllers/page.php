<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Page management controller
 *
 * @package midcom_core
 */
class midcom_core_controllers_page
{
    public function __construct($instance)
    {
        $this->configuration = $instance->configuration;
    }
    
    public function action_edit($route_id, &$data, $args)
    {
        if (!isset($_MIDGARD['page']))
        {
            throw new midcom_exception_notfound("No Midgard page found");
        }
        $data['page'] = new midgard_page();
        $data['page']->get_by_id($_MIDGARD['page']);
        // TODO: Datamanagerize
    }
}
?>