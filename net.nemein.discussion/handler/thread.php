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
        
        // Prepare datamanager
        $data['datamanager'] = new midcom_helper_datamanager2_datamanager($data['schemadb']);  
        
        $_MIDCOM->bind_view_to_object($this->_request_data['thread']);
        
        return true;
    }
    
    function _populate_post_toolbar($post)
    {
        $toolbar = new midcom_helper_toolbar();
    
        $toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "reply/{$post->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('reply'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_mail-reply.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_request_data['thread']->can_do('midgard:create'),
            )
        );
        
        if (   $post->can_do('midgard:update')
            && $_MIDCOM->auth->user
            && $post->metadata->creator != $_MIDCOM->auth->user->guid
            && $post->status < NET_NEMEIN_DISCUSSION_MODERATED)
        {
            if ($post->status > NET_NEMEIN_DISCUSSION_REPORTED_ABUSE)
            {
                $toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "report/{$post->guid}.html",
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('report abuse'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_help-agent.png',
                        MIDCOM_TOOLBAR_ENABLED =>  $post->can_do('midgard:update'),
                        MIDCOM_TOOLBAR_POST => true,
                        MIDCOM_TOOLBAR_POST_HIDDENARGS => array
                        (
                            'mark' => 'abuse',
                        )
                    )
                ); 
            }
            else
            {
                $toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "report/{$post->guid}.html",
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('confirm abuse'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                        MIDCOM_TOOLBAR_ENABLED => $post->can_do('net.nemein.discussion:moderation'),
                        MIDCOM_TOOLBAR_POST => true,
                        MIDCOM_TOOLBAR_POST_HIDDENARGS => array
                        (
                            'mark' => 'confirm_abuse',
                        )
                    )
                );
                $toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "report/{$post->guid}.html",
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('confirm junk'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                        MIDCOM_TOOLBAR_ENABLED => $post->can_do('net.nemein.discussion:moderation'),
                        MIDCOM_TOOLBAR_POST => true,
                        MIDCOM_TOOLBAR_POST_HIDDENARGS => array
                        (
                            'mark' => 'confirm_junk',
                        )
                    )
                );
                $toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "report/{$post->guid}.html",
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('not abuse'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/approved.png',
                        MIDCOM_TOOLBAR_ENABLED => $post->can_do('net.nemein.discussion:moderation'),
                        MIDCOM_TOOLBAR_POST => true,
                        MIDCOM_TOOLBAR_POST_HIDDENARGS => array
                        (
                            'mark' => 'not_abuse',
                        )
                    )
                );
            }      
        }
        return $toolbar;  
    }

    function _show_thread($handler_id, &$data)
    {
        switch($this->_config->get('display_thread_mode'))
        {
            case 'threaded':
                return $this->_show_thread_threaded($handler_id, $data);
                break;
            case 'flat':
            default:            
                return $this->_show_thread_flat($handler_id, $data);
                break;
        }
    }

    function _show_thread_threaded($handler_id, &$data)
    {   
        $qb = new org_openpsa_qbpager('net_nemein_discussion_post_dba', 'net_nemein_discussion_posts');
        $qb->results_per_page = $this->_config->get('display_posts');
        $qb->display_pages = $this->_config->get('display_pages');
        $qb->add_constraint('thread', '=', $data['thread']->id);
        $qb->add_constraint('status', '>=', NET_NEMEIN_DISCUSSION_REPORTED_ABUSE);
        $qb->add_order('replyto', 'ASC');
        $qb->add_order('metadata.published', 'ASC');

        $data['post_qb'] =& $qb;
        $posts = $qb->execute();

        // Make a tree of the posts
        $data['post_tree'] = array
        (
            'map' => array(),
            'replies' => array(),
        );
        $tree =& $data['post_tree'];
        $post_moves = 0;
        foreach($posts as $k => $post)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Putting post #{$post->id} to tree");
            debug_pop();
            if (empty($post->replyto))
            {
                // Root level post, parent is root of tree
                $parent =& $tree;
            }
            elseif (!isset($tree['map'][$post->replyto]))
            {
                // Problem, can't find parent in map, move to end of array...
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Can't find parent for post #{$post->id} (replyto: {$post->replyto}) in tree yet, moving to end for retrying", MIDCOM_LOG_INFO);
                debug_pop();
                $posts[] = $post;
                unset($posts[$k]);
                $post_moves++;
                if ($post_moves > count($posts))
                {
                    // We have moved the post around more times than we have posts, break out of this loop
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Too many retries to find parent for post #{$post->id} (replyto: {$post->replyto}) in tree", MIDCOM_LOG_WARN);
                    debug_print_r("tree dump", $tree);
                    debug_pop();
                    break;
                }
                continue;
            }
            else
            {
                // Normal reply
                $parent =& $tree['map'][$post->replyto];
            }
            $parent['replies'][$post->id] = array
            (
                'post' => $post,
                'replies' => array(),
            );
            $tree['map'][$post->id] =& $parent['replies'][$post->id];
        }
        midcom_show_style('view-thread-header');
        $this->_show_thread_threaded_tree_recursive($tree['replies'], 1, $data);
        midcom_show_style('view-thread-footer');
    }

    function _show_thread_threaded_tree_recursive($post_data, $level, &$data)
    {
        foreach ($post_data as $post_id => $data_array)
        {
            /*
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r("data_array for post #{$post_id} (level={$level})", $data_array);
            debug_pop();
            */

            $post =& $data_array['post'];
            if (   !is_a($post, 'net_nemein_discussion_post_dba')
                || empty($post->id))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("key #{$post_id} has invalid data on 'post' subkey", MIDCOM_LOG_WARN);
                debug_print_r('subkey value', $post);
                debug_pop();                
                continue;
            }
            $data['post'] =& $post;
            $data['thread_level'] = $level;
            
            if (! $data['datamanager']->autoset_storage($post))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("The datamanager for post {$post->id} could not be initialized, skipping it.", MIDCOM_LOG_ERROR);
                debug_print_r('Object was:', $post);
                debug_pop();
                continue;
            }
            $data['view_post'] = $data['datamanager']->get_content_html();
            $data['post_toolbar'] = $this->_populate_post_toolbar($post);
            
            midcom_show_style('view-thread-item');

            if (   isset($data_array['replies'])
                && !empty($data_array['replies']))
            {
                midcom_show_style('view-thread-sublevel-header');
                $this->_show_thread_threaded_tree_recursive($data_array['replies'], $level+1, $data);
                midcom_show_style('view-thread-sublevel-footer');
            }
    
            unset($data['thread_level']);
        }
    }

    function _show_thread_flat($handler_id, &$data)
    {   
        $qb = new org_openpsa_qbpager('net_nemein_discussion_post_dba', 'net_nemein_discussion_posts');
        $qb->results_per_page = $this->_config->get('display_posts');
        $qb->display_pages = $this->_config->get('display_pages');
        $qb->add_constraint('thread', '=', $data['thread']->id);
        $qb->add_constraint('status', '>=', NET_NEMEIN_DISCUSSION_REPORTED_ABUSE);
        $qb->add_order('metadata.published', 'ASC');

        $data['post_qb'] =& $qb;
        $posts = $qb->execute();
        
        $data['first_post'] =& $posts[0];
        
        midcom_show_style('view-thread-header');
        
        foreach ($posts as $i => $post)
        {
            $data['index_count'] = $i;
            $data['post'] =& $post;
            
            if (! $data['datamanager']->autoset_storage($post))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("The datamanager for post {$post->id} could not be initialized, skipping it.",  MIDCOM_LOG_ERROR);
                debug_print_r('Object was:', $post);
                debug_pop();
                continue;
            }
            $data['view_post'] = $data['datamanager']->get_content_html();
            
            $data['post_toolbar'] = $this->_populate_post_toolbar($post);
            
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
        if ($thread->node != $this->_topic->id)
        {
            return false;
        }
        
        if ($handler_id == 'read_redirect')
        {
            $qb = net_nemein_discussion_post_dba::new_query_builder();
            $qb->add_constraint('thread', '=', $thread->id);
            $qb->add_constraint('status', '>=', NET_NEMEIN_DISCUSSION_REPORTED_ABUSE);
            $qb->add_order('metadata.published', 'ASC'); 
            
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
        
        $data['post'] = $requested_post;
        
        $_MIDCOM->skip_page_style = true;
        $_MIDCOM->cache->content->content_type("text/xml");
        $_MIDCOM->header("Content-type: text/xml; charset=UTF-8");
        
        return true;
    }
    
    function _show_post($handler_id, &$data)
    {   
        // Prepare datamanager
        $data['datamanager'] = new midcom_helper_datamanager2_datamanager($data['schemadb']);  

        if (! $data['datamanager']->autoset_storage($data['post']))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The datamanager for post {$data['post']->id} could not be initialized, skipping it.");
            debug_print_r('Object was:', $data['post']);
            debug_pop();
            continue;
        }
        $data['view_post'] = $data['datamanager']->get_content_html();
        midcom_show_style('view-post-xml');
    }
}
?>