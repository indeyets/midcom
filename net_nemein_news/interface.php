<?php
/**
 * @package net_nemein_news
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * News listing component
 *
 * @package net_nemein_news
 */
class net_nemein_news extends midcom_core_component_baseclass
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
            'url' => $_MIDCOM->dispatcher->generate_url('update', array('name' => $object->name), $this->folder),
            'method' => 'GET',
            'label' => $_MIDCOM->i18n->get('key: edit', 'midcom_core'),
            'icon' => 'midcom_core/stock_icons/16x16/edit.png',
        );
        $actions['delete'] = array
        (
            'url' => $_MIDCOM->dispatcher->generate_url('delete', array('name' => $object->name), $this->folder),
            'method' => 'GET',
            'label' => $_MIDCOM->i18n->get('key: delete', 'midcom_core'),
            'icon' => 'midcom_core/stock_icons/16x16/trash.png',
        );
        
        return $actions;
    }
}
?>