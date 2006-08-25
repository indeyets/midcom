<?php
/**
 * @package net.nemein.discussion
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Discussion forum thread displayer
 *
 * @package net.nemein.discussion
 */
class net_nemein_discussion_handler_thread extends midcom_baseclasses_components_handler
{
    var $_toolbars;

    /**
     * Simple default constructor.
     */
    function net_nemein_discussion_handler_thread()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Can-Handle check against the current wikipage name. We have to do this explicitly
     * in can_handle already, otherwise we would hide all subtopics as the request switch
     * accepts all argument count matches unconditionally.
     */
    function _can_handle_thread($handler_id, $args, &$data)
    {
        $can_handle = false;
        
        $qb = net_nemein_discussion_thread_dba::new_query_builder();
        $qb->add_constraint('node', '=', $this->_topic->id);
        $qb->add_constraint('posts', '>', 0);
        $qb->add_constraint('name', '=', $args[0]);
        $result = $qb->execute();
        
        if (count($result) == 1)
        {
            $this->_request_data['thread'] =& $result[0];
            
            // Set metadata
            $_MIDCOM->set_pagetitle($this->_request_data['thread']->title);
            $breadcrumb = Array();
            $breadcrumb[] = Array
            (
                MIDCOM_NAV_URL => $this->_request_data['thread']->name,
                MIDCOM_NAV_NAME => $this->_request_data['thread']->title,
            );
            $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);
            
            $can_handle = true;
        } 
        
        return $can_handle;
    }
    
    function _handler_thread($handler_id, $args, &$data)
    {
        $this->_view_toolbar->add_item(
            array
            (
                MIDCOM_TOOLBAR_URL => "",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('back to forum'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/back.png',
                MIDCOM_TOOLBAR_ENABLED =>  true,
            )
        );
        $this->_node_toolbar->add_item(
            array
            (
                MIDCOM_TOOLBAR_URL => 'post.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create thread'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_mail-reply.png',
                MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:create', $this->_topic),
            )
        );        
        // TODO: Add moderation tools
        
        return true;
    }
    
    function _show_thread($handler_id, &$data)
    {   
        $qb = new org_openpsa_qbpager('net_nemein_discussion_post_dba', 'net_nemein_discussion_posts');
        $qb->results_per_page = $this->_config->get('display_posts');
        $qb->display_pages = $this->_config->get('display_pages');
        $qb->add_constraint('thread', '=', $this->_request_data['thread']->id);
        $qb->add_constraint('status', '>=', NET_NEMEIN_DISCUSSION_REPORTED_ABUSE);
        $qb->add_order('created', 'ASC'); 
        // TODO: add moderation checks
        $this->_request_data['post_qb'] =& $qb;       
        $posts = $qb->execute();
        
        midcom_show_style('view-thread-header');        
        
        foreach ($posts as $post)
        {
            $this->_request_data['post'] =& $post;
            
            $this->_request_data['post_toolbar'] = new midcom_helper_toolbar();
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            $this->_request_data['post_toolbar']->add_item(
                array
                (
                    MIDCOM_TOOLBAR_URL => "{$prefix}reply/{$post->guid}.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('reply'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_mail-reply.png',
                    MIDCOM_TOOLBAR_ENABLED =>  $_MIDCOM->auth->can_do('midgard:create', $this->_request_data['thread']),
                )
            );
            
            // FIXME: This should be a real button to avoid link prefetchers
            $this->_request_data['post_toolbar']->add_item(
                array
                (
                    MIDCOM_TOOLBAR_URL => "{$prefix}report/{$post->guid}.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('report abuse'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_help-agent.png',
                    MIDCOM_TOOLBAR_ENABLED =>  $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['thread']),
                )
            );            
            // TODO: Add other moderation buttons
            
            midcom_show_style('view-thread-item');
        }
        
        midcom_show_style('view-thread-footer');
    }
    

    /**
     * Jump to a specific post in a thread
     */
    function _handler_post($handler_id, $args, &$data)
    {
    
        $requested_post = new net_nemein_discussion_post_dba($args[0]);
        if (!$requested_post)
        {
            return false;
        }
        
        $thread = $requested_post->get_parent();
        
        $qb = net_nemein_discussion_post_dba::new_query_builder();
        $qb->add_constraint('thread', '=', $thread->id);
        $qb->add_constraint('status', '>=', NET_NEMEIN_DISCUSSION_REPORTED_ABUSE);
        $qb->add_order('created', 'ASC'); 
        
        $posts = $qb->execute();        
        $processed = 0;
        $page = 1;
        
        foreach ($posts as $post)
        {
            $processed++;
            if ($processed > $this->_config->get('display_posts'))
            {
                $page++;
                $processed = 1;
            }
         
            if ($post->guid == $requested_post->guid)
            {
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "{$thread->name}/?org_openpsa_qbpager_net_nemein_discussion_posts_page={$page}#{$requested_post->guid}");
                // This will exit
            }
        }
        return false;
    }
}
?>