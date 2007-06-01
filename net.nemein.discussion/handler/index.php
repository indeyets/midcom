<?php
/**
 * @package net.nemein.discussion
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Discussion forum index
 *
 * @package net.nemein.discussion
 */
class net_nemein_discussion_handler_index extends midcom_baseclasses_components_handler
{
    var $_toolbars;

    /**
     * Simple default constructor.
     */
    function net_nemein_discussion_handler_index()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _handler_index($handler_id, $args, &$data)
    {
        $this->_request_data['forum'] =& $this->_topic;   
        
        return true;
    }
    
    function _show_index($handler_id, &$data)
    {      
        // List threads
        $qb = new org_openpsa_qbpager('net_nemein_discussion_thread_dba', 'net_nemein_discussion_thread');
        $qb->results_per_page = $this->_config->get('display_threads');
        $qb->display_pages = $this->_config->get('display_pages');
        $qb->add_constraint('node', '=', $this->_topic->id);
        $qb->add_constraint('posts', '>', 0);
        $qb->add_order($this->_config->get('order_threads_by'), 'DESC');
        $threads = $qb->execute_unchecked();
        $this->_request_data['thread_qb'] =& $qb;
        
        midcom_show_style('view-index-header');
        
        if ($threads)
        {
            foreach ($threads as $thread)
            {
                $this->_request_data['thread'] =& $thread;  
                $this->_request_data['latest_post'] = new net_nemein_discussion_post_dba($thread->latestpost);               
                midcom_show_style('view-index-item');
            }
        }
        midcom_show_style('view-index-footer');
        
        // Find out subforums (only one level)
        $forums = array();
        $forum_qb = midcom_db_topic::new_query_builder();
        $forum_qb->add_constraint('up', '=', $this->_topic->id);
        $forum_qb->add_order('score');          
        $forum_qb->add_order('extra');
        $nodes = $forum_qb->execute();
        foreach ($nodes as $node)
        {
            if ($node->parameter('midcom', 'component') == 'net.nemein.discussion')
            {
                $forums[] = $node;
            }
        }
        
        if (count($forums) > 0)
        {
            midcom_show_style('view-forum-index-header');
            foreach ($forums as $forum)
            {
                $this->_request_data['latest_thread'] = null;
                $this->_request_data['latest_post'] = null;
                $this->_request_data['forum'] = $forum;
                $thread_qb = net_nemein_discussion_thread_dba::new_query_builder();
                $thread_qb->add_constraint('posts', '>', 0);
                $thread_qb->add_constraint('node', '=', $forum->id);
                $thread_qb->add_order('latestposttime', 'DESC');
                $thread_qb->set_limit(1);
                $latest_thread = $thread_qb->execute_unchecked();
                foreach ($latest_thread as $thread)
                {
                    $this->_request_data['latest_thread'] =& $thread;
                    $this->_request_data['latest_post'] = new net_nemein_discussion_post_dba($thread->latestpost);               
                }
                midcom_show_style('view-forum-index-item');
            }
            midcom_show_style('view-forum-index-footer');                
        }
    }
}

?>