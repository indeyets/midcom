<?php
/**
 * MidCOM wrapped class for access to stored queries
 */
 
class net_nemein_discussion_post_dba extends __net_nemein_discussion_post_dba
{
    function net_nemein_discussion_post_dba($id = null)
    {
        return parent::__net_nemein_discussion_post_dba($id);
    }
    
    function get_parent_guid_uncached()
    {
        // FIXME: Midgard Core should do this
        if ($this->thread != 0)
        {
            $parent = new net_nemein_discussion_thread_dba($this->thread);
            return $parent;
        }
        else
        {
            return null;
        }
    }
    
    /**
     * Marks the message as reported abuse
     */
    function report_abuse()
    {
        // Set the status
        $this->status = NET_NEMEIN_DISCUSSION_REPORTED_ABUSE;
        $updated = $this->update();
        
        // Log who reported it
        $this->_log_moderation('reported_abuse');
        
        return $updated;
    }
    
    function get_logs()
    {
        $log_entries = array();
        $logs = $this->list_parameters('net.nemein.discussion.log');
        foreach ($logs as $action => $details)
        {
            // TODO: Show everything only to moderators
            $log_action  = explode(':', $action);
            $log_details = explode(':', $details);
            if (count($log_action) == 2)
            {
                if ($log_details[0] == 'anonymous')
                {
                    $reporter = 'anonymous';
                }
                else
                {
                    $user =& $_MIDCOM->auth->get_user($log_details[0]);
                    $reporter = $user->name;
                }
                
                $log_entries[$log_action[1]] = array
                (
                    'action'   => $log_action[0],
                    'reporter' => $reporter,
                    'ip'       => $log_details[1],
                    'browser'  => $log_details[2],
                );
            }
        }
        return $log_entries;
    }
    
    function _log_moderation($action = 'marked_spam')
    {
        if ($_MIDCOM->auth->user)
        {
            $user =& $_MIDCOM->auth->user->get_storage();
            $reporter = $user->guid;
        }
        else
        {
            $reporter = 'anonymous';
        }
        $browser = str_replace(':', '_', $_SERVER['HTTP_USER_AGENT']);
        $this->parameter('net.nemein.discussion.log', "$action:".time(), "{$reporter}:{$_SERVER['REMOTE_ADDR']}:{$browser}");
    }
    
    function _on_loaded()
    {
        if ($this->subject == '')
        {
            $this->subject = substr($this->content, 0, 20).'...';
        }
        return true;
    }
    
    function _on_updated()
    {
        return $this->_update_thread_cache();
    }

    function _on_created()
    {
        return $this->_update_thread_cache();
    }
    
    /**
     * Update the cached 'posts' and 'latestpost' attributes of the thread
     */
    function _update_thread_cache()
    {
        $thread = $this->get_parent();
        $latest_post = new net_nemein_discussion_post_dba($thread->latestpost);

        $qb = net_nemein_discussion_post_dba::new_query_builder();
        $qb->add_constraint('thread', '=', $thread->id);
        $qb->add_constraint('status', '>=', NET_NEMEIN_DISCUSSION_REPORTED_ABUSE);
        $posts = $qb->execute();
        foreach ($posts as $post)
        {
            if ($post->created > $latest_post->created)
            {
                $latest_post = $post;
            }
        }
    
        if (   count($posts) != $thread->posts
            || $latest_post->id != $thread->latestpost)
        {
            if (count($posts) > 0)
            {
                $thread->posts = count($posts);
                $thread->latestpost = $latest_post->id;
                $thread->latestposttime = $latest_post->created;
                $thread->update();
            }
            else
            {
                // TODO: There are no visible posts any more, should we delete thread?
            }
        }
        return true;
    }
}
?>