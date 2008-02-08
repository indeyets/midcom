<?php
/**
 * @package net.nemein.discussion
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: fetchplazes.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Cron handler for erasing old, unpopular threads
 * @package net.nemein.discussion
 */
class net_nemein_discussion_cron_eraseunpopular extends midcom_baseclasses_components_cron_handler
{
    function _on_initialize()
    {
        return true;
    }

    /**
     * Fetches subscribed feeds and imports them
     */
    function _on_execute()
    {
        if (!$this->_config->get('remove_unpopular_threads'))
        {
            return;
        }
    
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('_on_execute called');
        if (!$_MIDCOM->auth->request_sudo('net.nemein.discussion'))
        {
            $msg = "Could not get sudo, aborting operation, see error log for details";
            $this->print_error($msg);
            debug_add($msg, MIDCOM_LOG_ERROR);
            debug_pop();
            return;
        }

        $qb = net_nemein_discussion_thread_dba::new_query_builder();
        echo gmdate('Y-m-d H:i:s', time() - 3600 * 24 * 30) . "<br />\n";
        $qb->add_constraint('metadata.created', '<', gmdate('Y-m-d H:i:s', time() - 3600 * 24 * 30));
        $qb->add_constraint('posts', '<=', 1);
        $threads = $qb->execute();
        foreach ($threads as $thread)
        {
            $post_qb = net_nemein_discussion_post_dba::new_query_builder();
            $post_qb->add_constraint('thread', '=', $thread->id);
            $posts = $post_qb->execute();
            foreach ($posts as $post)
            {
                $post->delete();
            }
            debug_add("Thread #{$thread->id} {$thread->title} had {$thread->posts} and was created on " . date('Y-m-d H:i:s', $thread->metadata->created) . ". Removing.");
            $thread->delete();
        }

        $_MIDCOM->auth->drop_sudo();
        
        debug_add('Done');
        debug_pop();
        return;
    }
}
?>