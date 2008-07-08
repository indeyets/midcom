<?php
/**
 * @package net.nehmer.comments
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Comments main comment class
 *
 * Comments link up to the object they refer to.
 *
 * @package net.nehmer.comments
 */
class net_nehmer_comments_comment extends __net_nehmer_comments_comment
{
    function __construct($id = null)
    {
        parent::__construct($id);
    }

    /**
     * DBA magic defaults which assign write privileges for all USERS, so that they can
     * add new comments at will.
     */
    function get_class_magic_default_privileges()
    {
        return Array (
            'EVERYONE' => Array(),
            'ANONYMOUS' => Array(),
            'USERS' => Array('midgard:create' => MIDCOM_PRIVILEGE_ALLOW),
        );
    }

    /**
     * Link to the parent object specified in the objectguid field.
     */
    function get_parent_guid_uncached()
    {
        return $this->objectguid;
    }

    /**
     * Returns a list of comments applicable to a given object, ordered by creation
     * date.
     * 
     * May be called statically.
     *
     * @param guid $guid The GUID of the object to bind to.
     * @return Array List of applicable comments.
     */
    function list_by_objectguid($guid, $limit=false, $order='ASC', $paging=false, $status = false)
    {
        if ($paging !== false)
        {
            $qb = new org_openpsa_qbpager('net_nehmer_comments_comment', 'net_nehmer_comments_comment');
            $qb->results_per_page = $paging;
        }
        else
        {
            $qb = net_nehmer_comments_comment::new_query_builder();
        }

        if (!is_array($status))
        {
            $status = net_nehmer_comments_comment::get_default_status();
        }

        $qb->add_constraint('status', 'IN', $status);

        $qb->add_constraint('objectguid', '=', $guid);
        
        if (   $limit
            && !$paging)
        {
            $qb->set_limit($limit);
        }
        
        if (version_compare(mgd_version(), '1.8', '>='))
        {        
            $qb->add_order('metadata.created', $order);
        }
        else
        {
            $qb->add_order('created', $order);
        }

        if ($paging !== false)
        {
            return $qb;
        }

        return $qb->execute();
    }

    /**
     * Returns a list of comments applicable to a given object
     * not diplaying empty comments or anonymous posts,
     * ordered by creation date.
     * 
     * May be called statically.
     *
     * @param guid $guid The GUID of the object to bind to.
     * @return Array List of applicable comments.
     */
    function list_by_objectguid_filter_anonymous($guid, $limit=false, $order='ASC', $paging=false, $status = false)
    {
        if ($paging !== false)
        {
            $qb = new org_openpsa_qbpager('net_nehmer_comments_comment', 'net_nehmer_comments_comment');
            $qb->results_per_page = $paging;
        }
        else
        {
            $qb = net_nehmer_comments_comment::new_query_builder();            
        }

        if (!is_array($status))
        {
            $status = net_nehmer_comments_comment::get_default_status();
        }

        $qb->add_constraint('status', 'IN', $status);

        $qb->add_constraint('objectguid', '=', $guid);
        $qb->add_constraint('author', '<>', '');
        $qb->add_constraint('content', '<>', '');

        if (   $limit
            && !$paging)
        {
            $qb->set_limit($limit);
        }

        if (version_compare(mgd_version(), '1.8', '>='))
        {        
            $qb->add_order('metadata.created', $order);
        }
        else
        {
            $qb->add_order('created', $order);
        }

        if ($paging !== false)
        {
            return $qb;
        }

        return $qb->execute();
    }

    /**
     * Returns the number of comments associated with a given object. This is intended for
     * outside usage to render stuff like "15 comments". The count is executed unchecked.
     * 
     * May be called statically.
     *
     * @return int Number of comments matching a given result. 
     */
    function count_by_objectguid($guid, $status = false)
    {

        $qb = net_nehmer_comments_comment::new_query_builder();

        if (!is_array($status))
        {
            $status = net_nehmer_comments_comment::get_default_status();
        }

        $qb->add_constraint('status', 'IN', $status);

        $qb->add_constraint('objectguid', '=', $guid);        
        return $qb->count_unchecked();
    }
    
    function report_abuse()
    {
        if ($this->status == NET_NEHMER_COMMENTS_MODERATED)
        {
            return false;
        }

        // Set the status
        if (   $this->can_do('net.nehmer.comments:moderation')
            && !$this->_sudo_requested)
        {
            $this->status = NET_NEHMER_COMMENTS_ABUSE;
        }
        else
        {
            $this->status = NET_NEHMER_COMMENTS_REPORTED_ABUSE;
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
        if ($this->status == NET_NEHMER_COMMENTS_MODERATED)
        {
            return false;
        }
        // Set the status
        if (   !$this->can_do('net.nehmer.comments:moderation')
            || $this->_sudo_requested)
        {
            return false;
        }

        $this->status = NET_NEHMER_COMMENTS_ABUSE;
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
        if ($this->status == NET_NEHMER_COMMENTS_MODERATED)
        {
            return false;
        }

        // Set the status
        if (   !$this->can_do('net.nehmer.comments:moderation')
            || $this->_sudo_requested)
        {
            return false;
        }

        $this->status = NET_NEHMER_COMMENTS_JUNK;
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
        if (   !$this->can_do('net.nehmer.comments:moderation')
            || $this->_sudo_requested)
        {
            return false;
        }

        // Set the status
        $this->status = NET_NEHMER_COMMENTS_MODERATED;
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
        $logs = $this->list_parameters('net.nehmer.comments:moderation_log');
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
        
        $this->set_parameter('net.nehmer.comments:moderation_log', implode(':', $log_action), implode(':', $log_details));
    }
    
    function get_default_status()
    {
        $view_status = array
        (
            NET_NEHMER_COMMENTS_NEW, 
            NET_NEHMER_COMMENTS_NEW_ANONYMOUS, 
            NET_NEHMER_COMMENTS_NEW_USER,
            NET_NEHMER_COMMENTS_MODERATED,
        );

        if (isset($this->_config))
        {
            if ($this->_config->get('show_reported_abuse_as_normal'))
            {
                $view_status[] = NET_NEHMER_COMMENTS_REPORTED_ABUSE;
            }
        }

        return $view_status;
    }
    
}

?>