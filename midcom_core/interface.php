<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM interface class
 *
 * @package midcom_core
 */
class midcom_core extends midcom_core_component_baseclass
{
    public function get_object_actions(&$object, $variant = null)
    {
        $actions = array();
        if (!$_MIDCOM->authorization->can_do('midgard:update', $object))
        {
            // User is not allowed to edit so we have no actions available
            return $actions;
        }
        
        // This is the general action available for a page: forms-based editing
        $actions['edit'] = array
        (
            'url' => $_MIDCOM->dispatcher->generate_url('page_update', array(), $object),
            'method' => 'GET',
            'label' => $_MIDCOM->i18n->get('key: edit', 'midcom_core'),
            'icon' => 'midcom_core/stock_icons/16x16/edit.png',
        );
        
        return $actions;
    }
}
?>