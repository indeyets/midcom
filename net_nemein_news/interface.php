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
    public function get_object_actions(midgard_article &$object, $variant = null)
    {
        $actions = array();
        
        if (!$_MIDCOM->authorization->can_do('midgard:update', $object))
        {
            // User is not allowed to edit so we have no actions available
            return $actions;
        }
        
        // This is the general action available for a page: forms-based editing
        $actions['update'] = array
        (
            'url' => $_MIDCOM->dispatcher->generate_url('update', array('name' => $object->name), $this->folder),
            'method' => 'GET',
            'label' => $_MIDCOM->i18n->get('key:update', 'midcom_core'),
            'icon' => 'midcom_core/stock_icons/16x16/update.png',
        );
        $actions['delete'] = array
        (
            'url' => $_MIDCOM->dispatcher->generate_url('delete', array('name' => $object->name), $this->folder),
            'method' => 'GET',
            'label' => $_MIDCOM->i18n->get('key:delete', 'midcom_core'),
            'icon' => 'midcom_core/stock_icons/16x16/delete.png',
        );
        
        return $actions;
    }

    public function get_create_actions(midgard_page $folder)
    {
        $actions = array();
        
        if ($folder->component != 'net_nemein_news')
        {
            return $actions;
        }

        if (!$_MIDCOM->authorization->can_do('midgard:create', $folder))
        {
            // User is not allowed to create articles so we have no actions available
            return $actions;
        }
        
        $actions['create'] = array
        (
            'url' => $_MIDCOM->dispatcher->generate_url('create', array(), $folder),
            'method' => 'GET',
            'label' => $_MIDCOM->i18n->get('key: create article', 'net_nemein_news'),
            'icon' => 'midcom_core/stock_icons/16x16/document.png',
        );
        
        return $actions;
    }
}
?>