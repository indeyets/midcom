<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Action introspection controllers
 *
 * @package midcom_core
 */
class midcom_core_controllers_actions
{
    public function __construct(midcom_core_component_interface $instance)
    {
        $this->configuration = $_MIDCOM->configuration;
    }
    
    public function get_object(array $args)
    {
        $_MIDCOM->authorization->require_user();

        $object = midgard_object_class::get_object_by_guid($args['guid']);
        if (!$object->guid)
        {
            throw new midcom_exception_notfound("Object {$args['guid']} not found");
        }
        
        $this->data['actions'] = $_MIDCOM->componentloader->get_object_actions($object);
    }

    public function get_categories(array $args)
    {
        $_MIDCOM->authorization->require_user();
        
        $this->data['categories'] = $_MIDCOM->componentloader->get_action_categories();
    }

    public function get_category(array $args)
    {
        $_MIDCOM->authorization->require_user();
        
        $categories = $_MIDCOM->componentloader->get_action_categories();
        if (!in_array($args['category'], $categories))
        {
            throw new midcom_exception_notfound("Category {$args['category']} not found");
        }

        $page = new midgard_page($args['guid']);
        if (!$page->guid)
        {
            throw new midcom_exception_notfound("Folder {$args['guid']} not found");
        }
        
        $this->data['actions'] = $_MIDCOM->componentloader->get_category_actions($args['category'], $page);
    }
}
?>
