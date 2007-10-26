<?php
/**
 * @package midcom.admin.folder
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: configuration.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Sort navigation order.
 *
 * This handler enables drag'n'drop sorting of navigation
 *
 * @package midcom.admin.folder
 */
class midcom_admin_folder_handler_order extends midcom_baseclasses_components_handler
{
    /**
     * Constructor metdot
     * 
     * @access public
     */
    public function midcom_admin_folder_handler_order ()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * This function will set the score.
     * 
     * @access private
     */
    private function _process_order_form()
    {
        // If the navigation order is changed, it will be saved first. After this it is possible
        // again to organize the folder
        if ($_POST['f_navorder'] != $this->_topic->parameter('midcom.helper.nav', 'navorder'))
        {
            $this->_topic->set_parameter('midcom.helper.nav', 'navorder', $_POST['f_navorder']);
            return false;
            // This will exit.
        }
        
        if (array_key_exists('midcom_admin_content_page_score', $_POST))
        {
            $count = count($_POST['midcom_admin_content_page_score']);
            
            foreach ($_POST['midcom_admin_content_page_score'] as $key => $id)
            {
                $article = new midcom_db_article($id);
                $article->score = (int) $key;
                $article->metadata->score = (int) $count - $key;
                
                // Get the original approval status
                $metadata =& midcom_helper_metadata::retrieve($article);
                $approval_status = false;
                
                // Get the approval status if metadata object is available
                if (   is_object($metadata)
                    && $metadata->is_approved())
                {
                    $approval_status = true;
                }
                
                if (!$article->update())
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Updating the article with id '{$id}' failed. Reason: ". mgd_errstr(), MIDCOM_LOG_ERROR);
                    debug_pop();
                    
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Saving the order failed, see error level log for details');
                    // This will exit
                }
                
                // Maintain the approval status - if the object had been approved before
                // it should still be kept as approved
                if ($approval_status)
                {
                    $metadata =& midcom_helper_metadata::retrieve($article);
                    $metadata->approve();
                }
            }
        }
        
        if (array_key_exists('midcom_admin_content_folder_score', $_POST))
        {
            $count = count($_POST['midcom_admin_content_folder_score']);
            
            foreach ($_POST['midcom_admin_content_folder_score'] as $key => $id)
            {
                $topic = new midcom_db_topic($id);
                $topic->score = (int) $key;
                $topic->metadata->score = (int) $count - $key;
                
                // Get the original approval status
                $metadata =& midcom_helper_metadata::retrieve($topic);
                $approval_status = false;
                
                // Get the approval status if metadata object is available
                if (   is_object($metadata)
                    && $metadata->is_approved())
                {
                    $approval_status = true;
                }
                
                if (!$topic->update())
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Updating the topic with id '{$id}' failed. Reason: ". mgd_errstr(), MIDCOM_LOG_ERROR);
                    debug_pop();
                    
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Saving the order failed, see error level log for details');
                    // This will exit
                }
                
                // Maintain the approval status - if the object had been approved before
                // it should still be kept as approved
                if ($approval_status)
                {
                    $metadata =& midcom_helper_metadata::retrieve($topic);
                    $metadata->approve();
                }
            }
        }
        
        if (array_key_exists('midcom_admin_content_mixed_score', $_POST))
        {
            $count = count($_POST['midcom_admin_content_mixed_score']);
            
            foreach ($_POST['midcom_admin_content_mixed_score'] as $key => $id)
            {
                $type = explode('_', $id);
                if ($type[2] === 'folder')
                {
                    $object = new midcom_db_topic($type[1]);
                }
                else
                {
                    $object = new midcom_db_article($type[1]);
                }
                
                if (!is_object($object))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Could not get the {$type[1]} with id '{$id}'. Reason: ". mgd_errstr(), MIDCOM_LOG_ERROR);
                    debug_pop();
                    
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Saving the order failed, see error level log for details');
                }
                
                // Get the original approval status
                $metadata =& midcom_helper_metadata::retrieve($object);
                $approval_status = false;
                
                $object->metadata->score = (int) $count - $key;
                
                // Get the approval status if metadata object is available
                if (   is_object($metadata)
                    && $metadata->is_approved())
                {
                    $approval_status = true;
                }
                
                $object->score = (int) $key;
                
                if (!$object->update())
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Updating the {$type[1]} with id '{$id}' failed. Reason: ". mgd_errstr(), MIDCOM_LOG_ERROR);
                    debug_pop();
                    
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Saving the order failed, see error level log for details');
                    // This will exit
                }
                
                // Maintain the approval status - if the object had been approved before
                // it should still be kept as approved
                if ($approval_status)
                {
                    $metadata =& midcom_helper_metadata::retrieve($object);
                    $metadata->approve();
                }
            }
        }
        
        return true;
    }
    
    /**
     * Handler for setting the sort order
     */
    function _handler_order($handler_id, $args, &$data)
    {
        // Include Scriptaculous JavaScript library to headers
        // Scriptaculous/scriptaculous.js
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL.'/Pearified/JavaScript/Prototype/prototype.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL.'/Pearified/JavaScript/Scriptaculous/scriptaculous.js');
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL.'/midcom.admin.folder/midcom-admin-order.css',
            )
        );
        
        $this->_topic->require_do('midgard:update');
        
        if (array_key_exists('f_cancel', $_REQUEST))
        {
            $_MIDCOM->relocate($_MIDCOM->permalinks->create_permalink($this->_topic->guid));
            // This will exit
        }
        
        if (array_key_exists('f_submit', $_REQUEST))
        {
            if ($this->_process_order_form())
            {
                $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('midcom.admin.folder'), $_MIDCOM->i18n->get_string('order saved'));
                $_MIDCOM->relocate($_MIDCOM->permalinks->create_permalink($this->_topic->guid));
                // This will exit
            }
        }
        
        // Add the view to breadcrumb trail
        $tmp = array();
        
        $tmp[] = array
        (
            MIDCOM_NAV_URL => '__ais/folder/order.html',
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('order navigation', 'midcom.admin.folder'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        
        // Hide the button in toolbar
        $this->_node_toolbar->hide_item('__ais/folder/order.html');

        // Set page title
        $data['folder'] = $this->_topic;
        $data['title'] = sprintf($_MIDCOM->i18n->get_string('order navigation in folder %s', 'midcom.admin.folder'), $data['folder']->extra);
        $_MIDCOM->set_pagetitle($data['title']);
        
        // Set the help object in the toolbar
        $this->_view_toolbar->add_help_item('navigation_order', 'midcom.admin.folder');
        
        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midcom.admin.folder');
        
        return true;
    }
    
    /**
     * Show the sorting 
     *
     * @access private
     */
    function _show_order($handler_id, &$data)
    {
        $this->_request_data['navorder'] = (int) $this->_topic->parameter('midcom.helper.nav', 'navorder');
        
        $this->_request_data['navorder_list'] = array
        (
            MIDCOM_NAVORDER_DEFAULT => $_MIDCOM->i18n->get_string('default sort order', 'midcom.admin.folder'),
            MIDCOM_NAVORDER_TOPICSFIRST => $_MIDCOM->i18n->get_string('folders first', 'midcom.admin.folder'),
            MIDCOM_NAVORDER_ARTICLESFIRST => $_MIDCOM->i18n->get_string('pages first', 'midcom.admin.folder'),
            MIDCOM_NAVORDER_SCORE => $_MIDCOM->i18n->get_string('by score', 'midcom.admin.folder'),
        );
        
        $this->_request_data['sort_order_header'] = $this->_request_data['navorder_list'][$this->_request_data['navorder']];

        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('up', '=', $this->_topic->id);
        $qb->add_constraint('metadata.navnoentry', '=', 0);
        $qb->add_order('metadata.score', 'DESC');
        //$qb->add_order('name');
        //$qb->add_order('extra');
        
        $this->_request_data['folders'] = $qb->execute();
        
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        if (!$this->_config->get('indexinnav'))
        {
            $qb->add_constraint('name', '<>', 'index');
        }
        $qb->add_constraint('up', '=', 0);
        $qb->add_order('metadata.score', 'DESC');
        //$qb->add_order('name');
        //$qb->add_order('title');
        
        $this->_request_data['pages'] = $qb->execute();
        
        // Show the header element, which allows to change the sorting order
        // and displays headers for the user
        midcom_show_style('midcom-admin-show-order-header');
        
        switch ($this->_topic->parameter('midcom.helper.nav', 'navorder'))
        {
            // If the sort order is 'Pages first'
            case MIDCOM_NAVORDER_ARTICLESFIRST:
                if (count($this->_request_data['pages']) > 1)
                {
                    midcom_show_style('midcom-admin-show-order-pages');
                }
                
                if (count($this->_request_data['folders']) > 1)
                {
                    midcom_show_style('midcom-admin-show-order-folders');
                }
                break;
            
            // If the sort order is 'Folders first'
            case MIDCOM_NAVORDER_TOPICSFIRST:
                if (count($this->_request_data['folders']) > 1)
                {
                    midcom_show_style('midcom-admin-show-order-folders');
                }
                
                if (count($this->_request_data['pages']) > 1)
                {
                    midcom_show_style('midcom-admin-show-order-pages');
                }
                break;
            
            // If the sort order is 'by score'
            case MIDCOM_NAVORDER_SCORE:
                $this->_request_data['mixed'] = array ();
                
                foreach ($this->_request_data['folders'] as $topic)
                {
                    $score = $this->_get_score($topic->score);
                    $this->_request_data['mixed'][$score . '_' . $topic->id . '_folder'] = $topic->extra;
                }
                
                foreach ($this->_request_data['pages'] as $article)
                {
                    $score = $this->_get_score($article->score);
                    $this->_request_data['mixed'][$score . '_' . $article->id . '_page'] = $article->title;
                }
                
                ksort($this->_request_data['mixed']);
                
                midcom_show_style('midcom-admin-show-order-mixed');
                break;
            
            // If the sort order is 'Default component sort order'
            case MIDCOM_NAVORDER_DEFAULT:
            default:
                if (count($this->_request_data['folders']) > 1)
                {
                    midcom_show_style('midcom-admin-show-order-folders');
                }
                else
                {
                    midcom_show_style('midcom-admin-show-order-empty');
                }
                
                midcom_show_style('midcom-admin-show-order-default');
                break;
            
        }
        
        if (   count($this->_request_data['folders']) < 2
            && count($this->_request_data['pages']) < 2)
        {
            midcom_show_style('midcom-admin-show-order-empty');
        }
        
        midcom_show_style('midcom-admin-show-order-footer');
    }
    
    /**
     * Fill a given integer with zeros for alphabetic ordering
     * 
     * @access private
     * @param int $int    Integer
     * @return string     String filled with leading zeros
     */
    private function _get_score($int)
    {
        $string = (string) $int;
        
        while (strlen($string) < 5)
        {
            $string = "0{$string}";
        }
        
        return $string;
    }
}
?>