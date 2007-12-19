<?php
/**
 * @package net.nemein.updatenotification
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * 
 * @package net.nemein.updatenotification
 */
class net_nemein_updatenotification_handler_index  extends midcom_baseclasses_components_handler 
{

    /**
     * Simple default constructor.
     */
    function net_nemein_updatenotification_handler_index()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * _on_initialize is called by midcom on creation of the handler. 
     */
    function _on_initialize()
    {
    }
    
    /**
     * The handler for the index article. 
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     * 
     */
    function _handler_index ($handler_id, $args, &$data)
    {
        $this->_request_data['name']  = "net.nemein.updatenotification";
        $this->_request_data['display_text_before_form'] = $this->_config->get('display_text_before_form');
        $this->_request_data['view_title'] = $this->_topic->extra;
        $this->_request_data['display_root_topic'] = $this->_config->get('display_root_topic');
        $this->_request_data['display_levels'] = $this->_config->get('display_levels');
        $this->_request_data['nap'] = new midcom_helper_nav();
        $this->_request_data['levels_shown'] = 0;
        $this->_request_data['user'] = new midcom_db_person($_MIDGARD['user']);
        $this->_request_data['preferred_notification_methods'] = $this->_config->get('preferred_notification_methods');

        $this->_update_breadcrumb_line($handler_id);
        
        $title = $this->_l10n_midcom->get('index');

        $_MIDCOM->set_pagetitle(":: {$title}");
        
        
        $root_topic_to_display = new midcom_db_topic($this->_request_data['display_root_topic']);
        if($root_topic_to_display->id != 0)
        {
            $node = $root_topic_to_display->id;
        }
        else
        {
            $node = $this->_request_data['nap']->get_root_node();
        }

        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('up', '=', $this->_request_data['nap']->get_root_node());
        $qb->add_order('score');
        $qb->add_order('name');
        $this->_request_data['root_nodes'] = $qb->execute();

        return true;
    }
    

    function _show_index($handler_id, &$data)
    {
        midcom_show_style('index-header');
        midcom_show_style('index-item-level-start');
        foreach($this->_request_data['root_nodes'] as $node)
        {
            $this->_request_data['node'] = $node;
            midcom_show_style('index-item');
            if($this->_request_data['display_levels'] > 1)
            {
                midcom_show_style('index-item-level-start');
                $this->_show_node_childs($node->id);
                midcom_show_style('index-item-level-end');
            }
        }
        midcom_show_style('index-item-level-end');
        midcom_show_style('index-footer');
    }
    
    function _show_node_childs($parent)
    {
        $this->_request_data['levels_shown']++;
        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('up', '=', $parent);
        $qb->add_order('score');
        $qb->add_order('name');
        $this->_request_data['root_nodes'] = $qb->execute();
        
        foreach($this->_request_data['root_nodes'] as $node)
        {
            $this->_request_data['node'] = $node;
            midcom_show_style('index-item');
            if($this->_request_data['levels_shown'] <= $this->_request_data['display_levels'])
            {
                midcom_show_style('index-item-level-start');
                $this->_show_node_childs($node->id);
                midcom_show_style('index-item-level-end');
            }
        }
        $this->_request_data['levels_shown']--;
    }
    
    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line()
    {
        $tmp = Array();

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "/",
            MIDCOM_NAV_NAME => $this->_l10n->get('index'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}
?>
