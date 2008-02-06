<?php
/**
 * @package net.nemein.discussion
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM DBA access to posts
 *
 * @package net.nemein.discussion
 */
class net_nemein_discussion_post_dba extends __net_nemein_discussion_post_dba
{
    function __construct($id = null)
    {
        return parent::__construct($id);
    }
    
    function get_parent_guid_uncached()
    {
        if ($this->replyto)
        {
            $parent = new net_nemein_discussion_post_dba($this->replyto);
        }
        else
        {
            $parent = new net_nemein_discussion_thread_dba($this->thread);
        }
        return $parent->guid;
    }    

    /**
     * Statically callable method to get parent guid when object guid is given
     * 
     * Uses midgard_collector to avoid unnecessary full object loads
     *
     * @param guid $guid guid of thread to get the parent for
     */
    function get_parent_guid_uncached_static($guid)
    {
        if (empty($guid))
        {
            return null;
        }
        $mc_post = net_nemein_discussion_post_dba::new_collector('guid', $guid);
        $mc_post->add_value_property('replyto');
        $mc_post->add_value_property('thread');
        if (!$mc_post->execute())
        {
            // Error
            return null;
        }
        $mc_post_keys = $mc_post->list_keys();
        list ($key, $copy) = each ($mc_post_keys);
        $up = $mc_post->get_subkey($key, 'replyto');
        if ($up === false)
        {
            // error
            return null;
        }
        if (!empty($up))
        {
            return net_nemein_discussion_post_dba::_get_parent_guid_uncached_static_post($up);
        }
        $thread = $mc_post->get_subkey($key, 'thread');
        if ($thread === false)
        {
            // error
            return null;
        }
        return net_nemein_discussion_post_dba::_get_parent_guid_uncached_static_thread($thread);
    }
    
    /**
     * Get thread guid statically
     *
     * used by get_parent_guid_uncached_static
     *
     * @param id $parent_id id of thread to get the guid for
     */
    function _get_parent_guid_uncached_static_thread($parent_id)
    {
        if (empty($parent_id))
        {
            return null;
        }
        $mc_parent = net_nemein_discussion_thread_dba::new_collector('id', $parent_id);
        $mc_parent->add_value_property('guid');
        if (!$mc_parent->execute())
        {
            // Error
            return null;
        }
        $mc_parent_keys = $mc_parent->list_keys();
        list ($key, $copy) = each ($mc_parent_keys);
        $parent_guid = $mc_parent->get_subkey($key, 'guid');
        if ($parent_guid === false)
        {
            // Error
            return null;
        }
        return $parent_guid;
    }

    /**
     * Get post guid statically
     *
     * used by get_parent_guid_uncached_static
     *
     * @param id $parent_id id of thread to get the guid for
     */
    function _get_parent_guid_uncached_static_post($parent_id)
    {
        if (empty($parent_id))
        {
            return null;
        }
        $mc_parent = net_nemein_discussion_post_dba::new_collector('id', $parent_id);
        $mc_parent->add_value_property('guid');
        if (!$mc_parent->execute())
        {
            // Error
            return null;
        }
        $mc_parent_keys = $mc_parent->list_keys();
        list ($key, $copy) = each ($mc_parent_keys);
        $parent_guid = $mc_parent->get_subkey($key, 'guid');
        if ($parent_guid === false)
        {
            // Error
            return null;
        }
        return $parent_guid;
    }

    function get_label()
    {
        return $this->subject;
    }

    /**
     * Marks the message as reported abuse
     */
    function report_abuse()
    {
        if ($this->status == NET_NEMEIN_DISCUSSION_MODERATED)
        {
            return false;
        }

        // Set the status
        if ($this->can_do('net.nemein.discussion:moderation'))
        {
            $this->status = NET_NEMEIN_DISCUSSION_ABUSE;
        }
        else
        {
            $this->status = NET_NEMEIN_DISCUSSION_REPORTED_ABUSE;
        }

        if ($this->update())
        {
            // Log who reported it
            $this->_log_moderation('reported_abuse');
            return true;
        }
        return false;
    }

    /**
     * Marks the message as confirmed abuse
     */
    function confirm_abuse()
    {
        if ($this->status == NET_NEMEIN_DISCUSSION_REPORTED_MODERATED)
        {
            return false;
        }

        // Set the status
        if (!$this->can_do('net.nemein.discussion:moderation'))
        {
            return false;
        }

        $this->status = NET_NEMEIN_DISCUSSION_ABUSE;
        if ($this->update())
        {
            // Log who reported it
            $this->_log_moderation('confirmed_abuse');
            return true;
        }
        return false;
    }

    /**
     * Marks the message as confirmed junk (spam)
     */
    function confirm_junk()
    {
        if ($this->status == NET_NEMEIN_DISCUSSION_REPORTED_MODERATED)
        {
            return false;
        }

        // Set the status
        if (!$this->can_do('net.nemein.discussion:moderation'))
        {
            return false;
        }

        $this->status = NET_NEMEIN_DISCUSSION_JUNK;
        if ($this->update())
        {
            // Log who reported it
            $this->_log_moderation('confirmed_junk');
            return true;
        }
        return false;
    }

    /**
     * Marks the message as not abuse
     */
    function report_not_abuse()
    {
        if (!$this->can_do('net.nemein.discussion:moderation'))
        {
            return false;
        }

        // Set the status
        $this->status = NET_NEMEIN_DISCUSSION_MODERATED;
        $updated = $this->update();

        if ($this->update())
        {
            // Log who reported it
            $this->_log_moderation('reported_not_abuse');
            return true;
        }
        return false;
    }

    function get_logs()
    {
        $log_entries = array();
        $logs = $this->list_parameters('net.nemein.discussion:moderation_log');
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
            $reporter = $_MIDCOM->auth->user->guid;
        }
        else
        {
            $reporter = 'anonymous';
        }
        $browser = str_replace(':', '_', $_SERVER['HTTP_USER_AGENT']);
        $date_string = gmdate('Ymd\This');
        
        $log_action = array
        (
            0 => $action,
            1 => $date_string
        );
        
        $log_details = array
        (
            0 => $reporter,
            1 => str_replace(':', '_', $_SERVER['REMOTE_ADDR']),
            2 => $browser
        );
        
        $this->set_parameter('net.nemein.discussion:moderation_log', implode(':', $log_action), implode(':', $log_details));
    }

    function _on_loaded()
    {
        if ($this->subject == '')
        {
            $this->subject = substr($this->content, 0, 20).'...';
        }
        return true;
    }

    function _on_updating()
    {
        if ($this->subject == '')
        {
            $this->subject = substr($this->content, 0, 20) . '...';
        }
        return parent::_on_updating();
    }

    function _on_updated()
    {
        return $this->_update_thread_cache();
    }

    function _on_created()
    {
        return $this->_update_thread_cache();
    }
    
    function _on_deleted()
    {
        return $this->_update_thread_cache();
    }

    /**
     * Update the cached 'posts' and 'latestpost' attributes of the thread
     */
    function _update_thread_cache()
    {
        if (!$_MIDCOM->auth->request_sudo('net.nemein.discussion'))
        {
            return true;
        }
        $thread = $this->get_parent();
        if (is_a($thread, 'net_nemein_discussion_post'))
        {
            // This post has up pointing to another post, setting the parent in that way
            while (!is_a($thread, 'net_nemein_discussion_thread'))
            {
                $thread = $thread->get_parent();
            }
        }
        
        if (   !$thread
            || !$thread->guid)
        {
            return false;
        }

        $latest_post = new net_nemein_discussion_post_dba($thread->latestpost);

        $qb = net_nemein_discussion_post_dba::new_query_builder();
        $qb->add_constraint('thread', '=', $thread->id);
        $qb->add_constraint('status', '>=', NET_NEMEIN_DISCUSSION_REPORTED_ABUSE);
        $qb->add_order('metadata.created', 'DESC');
        $posts = $qb->execute();

        if (   !$latest_post->guid
            && count($posts) > 0)
        {
            $latest_post = $posts[0];
        }
        
        foreach ($posts as $post)
        {
            if ($post->metadata->published > $latest_post->metadata->published)
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
                $thread->latestposttime = $latest_post->metadata->published;
                $thread->update();
            }
            else
            {
                // TODO: There are no visible posts any more, should we delete thread?
                $thread->posts = 0;
                $thread->update();
            }
        }
        
        if ($_MIDCOM->componentloader->load_graceful('net.nemein.tag'))
        {
            // Copy post tags to thread
            net_nemein_tag_handler::copy_tags($this, $thread, 'net.nemein.discussion');
        }
        
        $_MIDCOM->auth->drop_sudo();

        return true;
    }
}
?>