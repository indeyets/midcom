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

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
	 */
    function _handler_index($handler_id, $args, &$data)
    {
        $data['forum'] =& $this->_topic;

        // Set context data
        /**
         * TODO: Figure out the latest thread/post metadata_revised to get the correct timestamp
         * this should give us reasonably working caching but the MIDCOM_CONTEXT_LASTMODIFIED is
         * naturally wrong
         */
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        
        // Prepare datamanager
        $data['datamanager'] = new midcom_helper_datamanager2_datamanager($data['schemadb']);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_index($handler_id, &$data)
    {
        // List threads
        $qb = new org_openpsa_qbpager('net_nemein_discussion_thread_dba', 'net_nemein_discussion_thread');
        $qb->results_per_page = $this->_config->get('display_threads');
        $qb->display_pages = $this->_config->get('display_pages');
        $qb->add_constraint('node', '=', $this->_topic->id);
        $qb->add_constraint('posts', '>', 0);
        $qb->add_order('sticky', 'DESC');
        $qb->add_order($this->_config->get('order_threads_by'), 'DESC');
        $threads = $qb->execute_unchecked();
        $data['thread_qb'] =& $qb;
        $date = null;
        midcom_show_style('view-index-header');

        if ($threads)
        {
            foreach ($threads as $i => $thread)
            {
                if ($this->_config->get('order_threads_by') == 'latestposttime')
                {
                    $thread_date = date('Y-m-d', $thread->latestposttime);
                    $thread_time = $thread->latestposttime;
                }
                else
                {
                    $thread_date = date('Y-m-d', $thread->metadata->published);
                    $thread_time = $thread->metadata->published;
                }
                
                if ($date != $thread_date)
                {
                    $data['date'] = $thread_time;
                    if (!is_null($date))
                    {
                        midcom_show_style('view-index-date-footer');
                    }
                    midcom_show_style('view-index-date-header');
                    $date = $thread_date;
                }

                $data['index_count'] =& $i;
                $data['thread'] =& $thread;
                
                $data['latest_post'] = new net_nemein_discussion_post_dba($thread->latestpost);
                $data['view_latest_post'] = array();                
                if ($data['datamanager']->autoset_storage($data['latest_post']))
                {
                    $data['view_latest_post'] = $data['datamanager']->get_content_html();
                }
                
                $data['first_post'] = null;
                $data['view_first_post'] = array();  
                if ($thread->firstpost)
                {
                    $data['first_post'] = new net_nemein_discussion_post_dba($thread->firstpost);
              
                    if ($data['datamanager']->autoset_storage($data['first_post']))
                    {
                        $data['view_first_post'] = $data['datamanager']->get_content_html();
                    }
                }
                
                midcom_show_style('view-index-item');
            }
        }
        midcom_show_style('view-index-date-footer');
        midcom_show_style('view-index-footer');

        // Find out subforums (only one level)
        $forum_qb = midcom_db_topic::new_query_builder();
        $forum_qb->add_constraint('up', '=', $this->_topic->id);
        $forum_qb->add_constraint('component', '=', 'net.nemein.discussion');
        $forum_qb->add_order('score');
        $forum_qb->add_order('extra');
        $forums = $forum_qb->execute();

        if (count($forums) > 0)
        {
            midcom_show_style('view-forum-index-header');
            foreach ($forums as $forum)
            {
                $data['latest_thread'] = null;
                $data['latest_post'] = null;
                $data['first_post'] = null;
                $data['forum'] = $forum;
                $thread_qb = net_nemein_discussion_thread_dba::new_query_builder();
                $thread_qb->add_constraint('posts', '>', 0);
                $thread_qb->add_constraint('node', '=', $forum->id);
                $thread_qb->add_order('latestposttime', 'DESC');
                $thread_qb->set_limit(1);
                $latest_thread = $thread_qb->execute_unchecked();
                foreach ($latest_thread as $thread)
                {
                    $data['latest_thread'] =& $thread;
                    $data['latest_post'] = new net_nemein_discussion_post_dba($thread->latestpost);
                    
                    if ($thread->firstpost)
                    {
                        $data['first_post'] = new net_nemein_discussion_post_dba($thread->firstpost);
                    }
                }
                midcom_show_style('view-forum-index-item');
            }
            midcom_show_style('view-forum-index-footer');
        }
    }
}

?>