<?php
/**
 * @package net.nemein.discussion
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Discussion forum latest post displayer
 *
 * @package net.nemein.discussion
 */
class net_nemein_discussion_handler_latest extends midcom_baseclasses_components_handler
{
    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Shows N latest posts from this forum
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_latest($handler_id, $args, &$data)
    {
        if (count($args) > 0)
        {
            $display_comments = $args[0];
        }
        else
        {
            $display_comments = $this->_config->get('latest_item_count');
        }

        if ($handler_id == 'rss')
        {
            $_MIDCOM->skip_page_style = true;
        }

        // Start up the post QB
        $qb = net_nemein_discussion_post_dba::new_query_builder();
        $qb->add_order('metadata.published', 'DESC');
        $qb->add_constraint('status', '>=', NET_NEMEIN_DISCUSSION_REPORTED_ABUSE);
        $qb->set_limit($display_comments);

        // Get as many threads as we show posts
        $thread_qb = net_nemein_discussion_thread_dba::new_query_builder();
        $thread_qb->add_constraint('node', '=', $this->_topic->id);
        $thread_qb->add_constraint('posts', '>', 0);
        $thread_qb->add_order('latestposttime', 'DESC');
        $thread_qb->set_limit($display_comments);
        $threads = $thread_qb->execute_unchecked();

        $qb->begin_group('OR');
        foreach ($threads as $thread)
        {
            $qb->add_constraint('thread', '=', $thread->id);
        }
        $qb->end_group();
        $this->_request_data['latest_posts'] = $qb->execute_unchecked();

        // Set context data
        $_MIDCOM->set_pagetitle(sprintf($this->_request_data['l10n']->get('% latest posts'), $display_comments));
        $breadcrumb = Array();
        $breadcrumb[] = Array
        (
            MIDCOM_NAV_URL => "latest/{$display_comments}",
            MIDCOM_NAV_NAME => sprintf($this->_request_data['l10n']->get('% latest posts'), $display_comments),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);

        /**
         * TODO: Figure out the latest thread/post metadata_revised to get the correct timestamp
         * this should give us reasonably working caching but the MIDCOM_CONTEXT_LASTMODIFIED is
         * naturally wrong
         */
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);

        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_latest($handler_id, &$data)
    {
        if ($handler_id == 'rss')
        {
            $this->_show_rss(&$data);
        }
        else
        {
            $this->_show_latest_posts(&$data);
        }
    }

    /**
     * Shows N latest posts from all forums
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_latest_all($handler_id, $args, &$data)
    {
        if (count($args) > 0)
        {
            $display_comments = $args[0];
        }
        else
        {
            $display_comments = $this->_config->get('latest_item_count');
        }

        if ($handler_id == 'rss_all')
        {
            $_MIDCOM->skip_page_style = true;
        }

        // Start up the post QB
        $qb = net_nemein_discussion_post_dba::new_query_builder();
        $qb->add_order('metadata.published', 'DESC');
        $qb->add_constraint('status', '>=', NET_NEMEIN_DISCUSSION_REPORTED_ABUSE);
        $qb->set_limit($display_comments);

        // Get as many threads as we show posts
        $thread_qb = net_nemein_discussion_thread_dba::new_query_builder();
        $thread_qb->add_constraint('posts', '>', 0);
        $thread_qb->add_order('latestposttime', 'DESC');
        $thread_qb->set_limit($display_comments);

        // Find out subforums (only one level)
        $nodes = array();
        $nodes[] = $this->_topic->id;
        // FIXME: We can use collector here
        $forum_qb = midcom_db_topic::new_query_builder();
        $forum_qb->add_constraint('up', '=', $this->_topic->id);
        $forum_qb->add_constraint('component', '=', 'net.nemein.discussion');
        $forums = $forum_qb->execute();
        foreach ($forums as $forum)
        {
            $nodes[] = $forum->id;
        }
        $thread_qb->add_constraint('node', 'IN', $nodes);

        $threads = $thread_qb->execute();
        $qb->begin_group('OR');
        foreach ($threads as $thread)
        {
            $qb->add_constraint('thread', '=', $thread->id);
        }
        $qb->end_group();
        $this->_request_data['latest_posts'] = $qb->execute();

        // Set context data
        $_MIDCOM->set_pagetitle($this->_request_data['l10n']->get('latest'));
        $breadcrumb = Array();
        $breadcrumb[] = Array
        (
            MIDCOM_NAV_URL => "latest/",
            MIDCOM_NAV_NAME => $this->_request_data['l10n']->get('latest'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);

        /**
         * TODO: Figure out the latest thread/post metadata_revised to get the correct timestamp
         * this should give us reasonably working caching but the MIDCOM_CONTEXT_LASTMODIFIED is
         * naturally wrong
         */
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_latest_all($handler_id, &$data)
    {
        if ($handler_id == 'rss_all')
        {
            $this->_show_rss(&$data);
        }
        else
        {
            $this->_show_latest_posts(&$data);
        }
    }

    /**
     * Show latest posts in HTML format
     *
     * @param mixed &$data The local request data.
     */
    function _show_latest_posts(&$data)
    {
        if (count($this->_request_data['latest_posts']) > 0)
        {
            midcom_show_style('view-replylist-header');

            foreach ($this->_request_data['latest_posts'] as $post)
            {
                $this->_request_data['post'] =& $post;
                midcom_show_style('view-replylist-item');
            }

            midcom_show_style('view-replylist-footer');
        }
    }

    /**
     * Show latest posts as RSS feed
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_rss(&$data)
    {
        $nap = new midcom_helper_nav();
        $node = $nap->get_node($nap->get_current_node());

        $_MIDCOM->cache->content->content_type('text/xml');
        $_MIDCOM->header('Content-type: text/xml; charset=UTF-8');

        $rss = new UniversalFeedCreator();
        $rss->title = $node[MIDCOM_NAV_NAME];
        $rss->link = $node[MIDCOM_NAV_FULLURL];
        $rss->syndicationURL = "{$node[MIDCOM_NAV_FULLURL]}rss.xml";
        $rss->cssStyleSheet = false;

        if (count($this->_request_data['latest_posts']) > 0)
        {
            foreach ($this->_request_data['latest_posts'] as $post)
            {
                $item = new FeedItem();
                $item->title = $post->subject;
                $item->date = (int) $post->metadata->published;
                $item->author = $post->sendername;
                $item->description = Markdown($post->content);

                $thread = new net_nemein_discussion_thread_dba($post->thread);
                $forum = $nap->get_node($thread->node);
                $item->link = "{$forum[MIDCOM_NAV_FULLURL]}read/{$post->guid}/";
                $rss->addItem($item);
            }
        }

        echo $rss->createFeed('RSS2.0');
    }
}

?>