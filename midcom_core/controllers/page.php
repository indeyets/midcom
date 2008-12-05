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
        $this->configuration = $_MIDCOM->configuration;
    }
    
    public function action_show($route_id, &$data, $args)
    {
    }  
    
    public function action_edit($route_id, &$data, $args)
    {
        if (!isset($_MIDGARD['page']))
        {
            throw new midcom_exception_notfound("No Midgard page found");
        }
        $data['page'] = new midgard_page();
        $data['page']->get_by_id($_MIDGARD['page']);

        $_MIDCOM->authorization->require_do('midgard:update', $data['page']);

        if (isset($_POST['save']))
        {
            $data['page']->title = $_POST['title'];
            $data['page']->content = $_POST['content'];
            $data['page']->update();
            
            header("Location: {$_MIDCOM->context->prefix}");
            exit();
        }
    }
}
?>