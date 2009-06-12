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
    public function __construct($instance)
    {
        $this->configuration = $_MIDCOM->configuration;
    }
    
    public function get_object($args)
    {
        $_MIDCOM->authorization->require_user();

        $object = midgard_object_class::get_object_by_guid($args['guid']);
        if (!$object->guid)
        {
            throw new midcom_exception_notfound("Object {$args['guid']} not found");
        }
        
        $this->data['actions'] = $_MIDCOM->componentloader->get_object_actions($object);
    }
}
?>
