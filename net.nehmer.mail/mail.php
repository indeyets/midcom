<?php
/**
 * @package net.nehmer.mail
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * On-Site Mail System Mail class
 *
 * @package net.nehmer.mail
 */
class net_nehmer_mail_mail extends __net_nehmer_mail_mail
{
    function net_nehmer_mail_mail($id = null)
    {
        parent::__net_nehmer_mail_mail($id);
    }

    function _on_loaded()
    {
        return true;
    }

    /**
     * This is a small helper which prepares a query builder ready to query the mailboxes
     * this mail belongs to
     *
     * @return midcom_core_querybuilder The prepared Querybuilder.
     */
    function get_qb_mailboxes()
    {
        $qb = net_nehmer_mail_mailbox::new_query_builder();
        $qb->add_constraint('guid', '=', $this->mailbox);
        
        return $qb;
    }

    /**
     * The get_parent_guid_uncached method links to the owning mailbox. If the mailbox cannot be resolved,
     * the error is logged but ignored silently, to allow for error handling.
     */
    function get_parent_guid_uncached()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $mailbox_guid = false;
        
        $mailbox = $this->get_mailbox();
        
        if (! $mailbox)
        {
            $ob = net_nehmer_mail_mailbox::get_outbox();
            $mailbox_guid = $ob->guid;
        }
        else
        {
            $mailbox_guid = $mailbox->guid;            
        }
        
        debug_pop();
        return $mailbox_guid;
    }

    /**
     * Returns an instance of the owning mailbox. A new object is created.
     *
     * @return net_nehmer_mail_mailbox The mailbox of this mail
     */
    function get_mailbox()
    {
        if (! isset($_MIDCOM->auth->user->guid))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("No user logged in.");
            debug_pop();
            
            if ($this->sender != '')
            {
                $mailbox = net_nehmer_mail_mailbox::get_outbox($_MIDCOM->auth->get_user($this->sender));
                if ($mailbox)
                {
                    return $mailbox;
                }
            }
            
            return false;
        }
        
        $mailbox = new net_nehmer_mail_mailbox($this->mailbox);
        
        return $mailbox;
    }
    
    function set_status($new_status)//,$user_id=false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        debug_add("set status on mail {$this->id} to {$new_status}");// for user {$user_id}");        
        
        $this->status = $new_status;

        if (! $this->update())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Warning, we could not change the status of mail {$this->id}. Ignoring silently.", MIDCOM_LOG_WARN);
            debug_print_r('Mail was:', $this);
            debug_pop();
            return false;
        }
        
        debug_pop();
        return true;
    }
    
    /**
     * Returns an instance of the mailboxes that has this message.
     *
     * @return Array of net_nehmer_mail_mailbox objects or empty array on failure
     */
    function get_other_mailboxes()
    {
        $qb = $this->get_qb_mailboxes();
        $qb->add_constraint('owner', '<>', $_MIDCOM->auth->user->guid);
        $results = $qb->execute();
        
        if (count($results) < 1)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("No mailbox founded for mail {$this->id} with owner different than {$_MIDCOM->auth->user->guid}.");
            debug_pop();
            return array();
        }
        
        return $results;
    }
    
    function get_receivers($include_sender=false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $receivers = array();
        
        if (! isset($_MIDCOM->auth->user->guid))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("No user logged in.");
            debug_pop();
            return $receivers;
        }
        
        $_MIDCOM->auth->request_sudo();
        
        $qb = net_nehmer_mail_mail::new_query_builder();
        $qb->add_constraint('parentmail', '=', $this->parentmail);
        
        if (!$include_sender)
        {
            $user =& $_MIDCOM->auth->user->get_storage();
            $qb->add_constraint('owner', '<>', $user->id);
        }

        $results = $qb->execute();
        
        if (count($results) < 1)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("No parent mails founded for mail {$this->id} with parentmail id {$this->parentmail}");
            debug_pop();
            return $receivers;
        }
        
        foreach ($results as $result)
        {
            $user =& $_MIDCOM->auth->get_user($result->owner);
            $receivers[] =& $user->get_storage();
        }
        
        $_MIDCOM->auth->drop_sudo();
        
        debug_pop();
        return $receivers;
    }
    
    function get_receiver_list($include_sender=false)
    {   
        $receivers = $this->get_receivers($include_sender);
        $names = array();
        foreach ($receivers as $k => $receiver)
        {
            $names[] = $receiver->name;
        }

        return $names;
    }
    
    function save_receiver_list()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $list = $this->get_receiver_list();
        
        if (empty($list))
        {
            $list = array("no receivers found");
        }
        
        $this->receiverlist = serialize($list);

        if (! $this->update())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Warning, we could not save mails {$this->id} receiver list. Ignoring silently.", MIDCOM_LOG_WARN);
            debug_print_r('Mail was:', $this);
            debug_pop();
            return false;
        }
        
        debug_pop();
    }
    
    function list_receivers()
    {
        if ($this->receiverlist == '')
        {
            $this->save_receiver_list();
        }
        
        echo implode(", ",unserialize($this->receiverlist));
    }
    
    function deliver_to(&$receivers)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        debug_print_r('Receivers: ',$receivers);
        debug_pop();
        
        $_MIDCOM->auth->request_sudo();                
        foreach ($receivers as $k => $receiver)
        {
            $inbox =& net_nehmer_mail_mailbox::get_inbox($receiver);
            
            if (   !$_MIDCOM->auth->can_do('net.nehmer.mail:ignore_quota', $inbox)
                && $inbox->is_over_quota())
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Couldn't send message to user {$receiver->id}. Reason: Inbox full.");
                debug_print_r('Mailbox object was:', $inbox);
                debug_pop();
                $_MIDCOM->uimessages->add(
                    $this->_l10n->get('net.nehmer.mail'),
                    sprintf($this->_l10n->get('mailbox full for user %s'), $receiver->name),
                    'warning'
                );
                continue;
            }
            
            $mail = new net_nehmer_mail_mail();
            $mail->mailbox = $inbox->guid;
            $mail->sender = $this->owner;
            $mail->subject = $this->subject;
            $mail->body = $this->body;
            $mail->received = time();            
            $mail->owner = $receiver->id;
            $mail->status = NET_NEHMER_MAIL_STATUS_UNREAD;
            $mail->parentmail = $this->id;

            if (! $mail->create())
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_print_r('Mailbox object was:', $inbox);
                debug_print_r('Mail object was:', $this);
                debug_pop();
                $_MIDCOM->uimessages->add(
                    $this->_l10n->get('net.nehmer.mail'),
                    sprintf($this->_l10n->get('mail delivery failed to user %s'), $receiver->name),
                    'warning'
                );
                continue;
                //$_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a Mail record for user {$receiver->id}. See the debug level log for details.");
                // This will exit.
            }
            
            $user =& $_MIDCOM->auth->get_user($receiver->guid);
            
            $this->set_privilege('midgard:read', $user);

            $mail->set_privilege('midgard:read', $user);
            $mail->set_privilege('midgard:delete', $user);
            $mail->set_privilege('midgard:owner', $user);
            //$mail->set_privilege('midgard:read');
            $mail->unset_privilege('midgard:owner');
            
            debug_add("delivered to user {$receiver->id} to mailbox {$inbox->id}");

        }
        $_MIDCOM->auth->drop_sudo();
        
        $outbox = net_nehmer_mail_mailbox::get_outbox();
        if ($outbox)
        {
            // Check against Quota, if exceeded, drop old mails accordingly.
            if ($outbox->is_over_quota())
            {
                $to_delete = $outbox->get_message_count() - $outbox->quota + 1;
                $qb = $outbox->get_qb_mails();
                $qb->add_order('received');
                $qb->set_limit($to_delete);
                $mails = $qb->execute();
                foreach ($mails as $mail)
                {
                    $mail->delete();
                }
            }
            
            $this->parentmail = $this->id;
            $this->mailbox = $outbox->guid;
            
            debug_add("added to current user outbox");
        }        
    }

    /**
     * This is a helper which lists all mails marked as deleted belonging to current user
     * This can be called as static.
     *
     * @param string $order A regular ordering constraint, consisting of a field name
     *     and the optional prefix 'reverse'. The default is 'reverse received'.
     * @return Array A list of found mails, or false on failure.
     */
    function list_deleted_mails()
    {   
        $user =& $_MIDCOM->auth->user->get_storage();
        
        $qb = new midgard_query_builder('net_nehmer_mail_mail');
        $qb->add_constraint('owner', '=', $user->id);
        $qb->include_deleted();
        $qb->add_constraint('metadata.deleted', '<>', 0);
        
        $results = $qb->execute();
        
        return $results;
    }
    
    function list_unread_mails($order = 'reverse received')
    {
        $qb = net_nehmer_mail_mail::new_query_builder();
        $qb->add_order($order);
        $qb->add_constraint('status', '=', NET_NEHMER_MAIL_STATUS_UNREAD);
        
        $results = $qb->execute();
        
        return $results;
    }
    function list_unread_mails_count()
    {   
        $qb = net_nehmer_mail_mail::new_query_builder();
        $qb->add_constraint('status', '=', NET_NEHMER_MAIL_STATUS_UNREAD);
        
        $results = $qb->count();
        
        return $results;
    }

    /**
     * DBA magic defaults which assign write privileges for all USERS, so that they can freely
     * create mails without the need to sudo of the component. Also, we deny read unconditionally,
     * as read privileges are set during creation for the sender, and are inherited from the
     * mailbox for the receiver.
     */
    function get_class_magic_default_privileges()
    {
        return Array (
            'EVERYONE' => Array('midgard:read' => MIDCOM_PRIVILEGE_DENY),
            'ANONYMOUS' => Array(),
            'USERS' => Array('midgard:create' => MIDCOM_PRIVILEGE_ALLOW),
        );
    }

    /**
     * Returns the HMTL-formatted body of the message. Uses the net.nehmer.markdown
     * library.
     *
     * @return string The HTML-Formatted body.
     */
    function get_body_formatted()
    {
        static $markdown = null;
        if (! $markdown)
        {
            $markdown = new net_nehmer_markdown_markdown();
        }

        return $markdown->render($this->body);
    }

}