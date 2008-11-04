<?php

/**
 * @package net.nemein.discussion
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Forum MidCOM interface class.
 *
 * Defines the privilege net.nemein.discussion::moderation to supersede the original
 * moderator group.
 *
 * @package net.nemein.discussion
 */
class net_nemein_discussion_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function __construct()
    {
        parent::__construct();

        $this->_component = 'net.nemein.discussion';
        $this->_autoload_files = Array
        (
            'viewer.php',
            'navigation.php',
            'thread.php',
            'post.php',
        );
        $this->_autoload_libraries = Array(
            'midcom.helper.datamanager2',
            'de.bitfolge.feedcreator',
            'org.openpsa.qbpager',
            'net.nehmer.markdown',
            'midcom.helper.xml',
        );
        $this->_acl_privileges['moderation'] = MIDCOM_PRIVILEGE_DENY;

        // New messages enter at 4, and can be lowered or raised
        define('NET_NEMEIN_DISCUSSION_JUNK', 1);
        define('NET_NEMEIN_DISCUSSION_ABUSE', 2);
        define('NET_NEMEIN_DISCUSSION_REPORTED_ABUSE', 3);
        define('NET_NEMEIN_DISCUSSION_NEW', 4);
        define('NET_NEMEIN_DISCUSSION_NEW_USER', 5);
        define('NET_NEMEIN_DISCUSSION_MODERATED', 6);
    }
    
    function _on_watched_dba_create($vote)
    {
        if (!$this->_data['config']->get('create_thread_from_quickpoll'))
        {
            return;
        }
    
        if (!$_MIDCOM->componentloader->load_graceful('net.nemein.quickpoll'))
        {
            // No quickpoll installed, skip
            return;
        }
        
        if (!is_a($vote, 'net_nemein_quickpoll_vote_dba'))
        {
            return;
        }
        
        if (   !isset($vote->comment)
            || !$vote->comment)
        {
            return;
        }
        
        $forum = new midcom_db_topic($this->_data['config']->get('create_thread_from_quickpoll_forum'));
        if (   !$forum
            || $forum->component != 'net.nemein.discussion')
        {
            return;
        }
        
        $poll = new midcom_db_article($vote->article);
        if (   !$poll
            || !$poll->title)
        {
            return;
        }
        
        $thread = new net_nemein_discussion_thread_dba();
        $thread->node = $forum->id;
        $thread->title = $poll->title;
        if (!$thread->create())
        {
            return;
        }
        $thread->parameter('net.nemein.quickpoll', 'vote', $vote->guid);
        $thread->parameter('net.nemein.quickpoll', 'quickpoll', $poll->guid);
        $thread->parameter('net.nemein.discussion', 'thread_type', 'quickpoll');
        
        $post = new net_nemein_discussion_post_dba();
        $post->subject = $poll->title;
        $post->content = $vote->comment;
        $post->thread = $thread->id;
        $post->status = NET_NEMEIN_DISCUSSION_NEW;
        if (!$post->create())
        {
            $thread->delete();
            return;
        }
        
        $thread->name = midcom_generate_urlname_from_string(strftime('%x %X', time()) . ": {$post->subject}");
        $thread->firstpost = $post->id;
        $thread->latestpost = $post->id;
        $thread->latestposttime = (int) $post->metadata->published;
        if (!$thread->update())
        {
            $post->delete();
            $thread->delete();
        }
    }

    /**
     * Iterate over all articles and create index record using the datamanager indexer
     * method.
     */
    function _on_reindex($topic, $config, &$indexer)
    {
        $thread_qb = net_nemein_discussion_thread_dba::new_query_builder();
        $thread_qb->add_constraint('node', '=', $topic->id);
        $thread_qb->add_constraint('posts', '>', 0);
        $threads = $thread_qb->execute();
        
        if (count($threads) == 0)
        {
            debug_pop();
            return true;
        }
        
        $qb = net_nemein_discussion_post_dba::new_query_builder();
        $qb->add_constraint('status', '>=', 'NET_NEMEIN_DISCUSSION_REPORTED_ABUSE');
        $qb->begin_group('OR');
        foreach ($threads as $thread)
        {
            echo "{$thread->node} {$thread->title}\n";
            $qb->add_constraint('thread', '=', $thread->id);
        }
        $qb->end_group();
        $posts = $qb->execute();
        
        if ($posts)
        {
            $schemadb = midcom_helper_datamanager2_schema::load_database($config->get('schemadb'));
            $datamanager = new midcom_helper_datamanager2_datamanager($schemadb);
            if (! $datamanager)
            {
                debug_add('Warning, failed to create a datamanager instance with this schemapath:' . $config->get('schemadb'),
                    MIDCOM_LOG_WARN);
                continue;
            }

            foreach ($posts as $post)
            {
                echo "{$post->subject} {$post->status}\n";
                if (! $datamanager->autoset_storage($post))
                {
                    debug_add("Warning, failed to initialize datamanager for Post {$post->id}. Skipping it.", MIDCOM_LOG_WARN);
                    continue;
                }

                net_nemein_discussion_viewer::index($datamanager, $indexer, $topic);
            }
        }

        debug_pop();
        return true;
    }

    /**
     * Simple lookup method which tries to map the guid to a post of out topic.
     */
    function _on_resolve_permalink($topic, $config, $guid)
    {
        $thread = new net_nemein_discussion_thread_dba($guid);
        if (   $thread
            && $thread->guid)
        {
            if ($thread->node != $topic->id)
            {
                return null;
            }
            return "{$thread->name}/";
        }
        
        $post = new net_nemein_discussion_post_dba($guid);
        if (   $post
            && $post->guid)
        {
            $thread = new net_nemein_discussion_thread_dba($post->thread);
            if ($thread->node != $topic->id)
            {
                return null;
            }
            return "read/{$post->guid}/";
        }
        
        return null;
    }

}

?>