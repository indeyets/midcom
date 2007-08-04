<?php
/**
 * @package net.nehmer.mail
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * On-Site Mail System Mailbox class
 *
 * @package net.nehmer.mail
 */

class net_nehmer_mail_mailbox extends __net_nehmer_mail_mailbox
{
    /**
     * Internal variable, caches the unseen message count for performance reasons.
     *
     * @var int
     * @access private
     */
     var $_unseen_count = -1;

    /**
     * Internal variable, caches the total message count for performance reasons.
     *
     * @var int
     * @access private
     */
    var $_message_count = -1;

    /** Empty constructor calling parent only. */
    function net_nehmer_mail_mailbox($id = null)
    {
        parent::__net_nehmer_mail_mailbox($id);
    }

    /**
     * The get_parent_guid_uncached method links to the person owning the mailbox.
     */
    function get_parent_guid_uncached()
    {
        return $this->owner;
    }
    
    /**
     * Returns inboxes view url without any prefix ie. view/mailbox/INBOX.html
     */
    function get_view_url()
    {
        $url_prefix = "mailbox/view";
        $url_suffix = $this->guid;
        
        if (   strtolower($this->name) == 'inbox'
            || strtolower($this->name) == 'outbox')
        {
            $url_suffix = strtolower($this->name);
        }
        
        return "{$url_prefix}/{$url_suffix}";
    }
    
    /**
     * This function lists all mailboxes for the current user. If no user is authenticated,
     * an ACCESS DENIED error is triggered.
     *
     * This call will automatically create inbox and outbox.
     *
     * @param string $order A set_order compatible ordering field specification.
     * @return Array List of Mailboxes.
     */
    function list_mailboxes($order = 'name')
    {
        $_MIDCOM->auth->require_valid_user();
        $qb = net_nehmer_mail_mailbox::get_qb_list_by_user($_MIDCOM->auth->user);
        $qb->add_order($order);
        $query_result = $qb->execute();

        $result = Array();
        foreach ($query_result as $mailbox)
        {
            $result[$mailbox->name] = $mailbox;
        }

        if (! array_key_exists('INBOX', $result))
        {
            $result['INBOX'] = net_nehmer_mail_mailbox::autocreate_inbox();
        }
        if (! array_key_exists('OUTBOX', $result))
        {
            $result['OUTBOX'] = net_nehmer_mail_mailbox::autocreate_outbox();
        }

        return $result;
    }

    /**
     * This function returns a given mailbox of a user, defaulting to the Inbox
     * currently authenticated user. If you request the current user's mailbox without
     * having a user authenticated, an ACCESS DENIED error is triggered.
     *
     * This function may be called statically.
     *
     * @param string $mailbox The name of the mailbox to look up.
     * @param midcom_core_user $user The user to look up, defaulting to the currently authenticated user.
     * @return net_nehmer_mail_mailbox The requested Mailbox, or false, if it was not found.
     */
    function get_mailbox($mailbox, $user = null)
    {
        if ($user === null)
        {
            $_MIDCOM->auth->require_valid_user();
            $qb = net_nehmer_mail_mailbox::get_qb_list_by_user($_MIDCOM->auth->user);
        }
        else
        {
            $qb = net_nehmer_mail_mailbox::get_qb_list_by_user($user);
        }
        $qb->add_constraint('name', '=', $mailbox);
        $result = $qb->execute();

        if (count($result) == 0)
        {
            return false;
        }

        return $result[0];
    }

    /**
     * Returns the Inbox of a given user, defaulting to the currently authenticated user.
     *
     * This function may be called statically.
     *
     * @param midcom_core_user $user The user to look up, defaulting to the currently authenticated user.
     * @return net_nehmer_mail_mailbox The Inbox of the current user, or false, if it was
     *     not found.
     */
    function get_inbox($user = null)
    {
        $inbox = net_nehmer_mail_mailbox::get_mailbox('INBOX', $user);
        if (! $inbox)
        {
            $inbox = net_nehmer_mail_mailbox::autocreate_inbox($user);
        }
        return $inbox;
    }

    /**
     * Returns the Outbox of a given user, defaulting to the currently authenticated user.
     *
     * This function may be called statically.
     *
     * @param midcom_core_user $user The user to look up, defaulting to the currently authenticated user.
     * @return net_nehmer_mail_mailbox The Outbox of the current user, or false, if it was
     *     not found.
     */
    function get_outbox($user = null)
    {
        $outbox = net_nehmer_mail_mailbox::get_mailbox('OUTBOX', $user);
        if (! $outbox)
        {
            $outbox = net_nehmer_mail_mailbox::autocreate_outbox($user);
        }
        return $outbox;
    }

    /**
     * This function returns a new query builder instance which allows you to list mailboxes
     * by a given user. You may pass a midcom_core_user object, any midcom_person subclass,
     * an ID or an GUID to this function.
     *
     * This function may be called statically.
     *
     * @param $user A user reference, either a midcom_core_user, a midcom_person or subclass thereof,
     *     person ID or GUID.
     * @return midcom_core_querybuilder The prepared Querybuilder or false on failure.
     */
    function get_qb_list_by_user($user)
    {
        $guid = '';

        if (mgd_is_guid($user))
        {
            $guid = $user;
        }
        else if (is_numeric($user))
        {
            $person = new midcom_db_person($user);
            if (! $person)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to load the person ID {$user} from disk: " . mgd_errstr(), MIDCOM_LOG_INFO);
                debug_pop();
                return false;
            }
            $guid = $person->guid;
        }
        else if (is_object($user))
        {
            $guid = $user->guid;
        }
        else
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Incompatible argument type passed.', MIDCOM_LOG_INFO);
            debug_print_r('Argument was:', $user);
            debug_pop();
            return false;
        }

        $qb = net_nehmer_mail_mailbox::new_query_builder();
        $qb->add_constraint('owner', '=', $guid);
        return $qb;
    }

    /**
     * This is a small helper which prepares a query builder ready to query the mails
     * in this mailbox.
     *
     * If you don't have permission to list this mailboxes mails, an access denied error
     * is triggered.
     *
     * @return midcom_core_querybuilder The prepared Querybuilder.
     */
    function get_qb_mails()
    {
        $_MIDCOM->auth->require_do('net.nehmer.mail:list_mails', $this);

        $qb = net_nehmer_mail_mail::new_query_builder();
        $qb->add_constraint('mailbox', '=', $this->guid);
                
        return $qb;
    }

    /**
     * This is a helper which lists all mails belonging to this mailbox.
     *
     * @param string $order A regular ordering constraint, consisting of a field name
     *     and the optional prefix 'reverse'. The default is 'reverse received'.
     * @return Array A list of found mails, or false on failure.
     */
    function list_mails($order = 'reverse received')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $qb = $this->get_qb_mails();
        $qb->add_order($order);
        $results = $qb->execute();
        
        debug_pop();
        return $results;
    }

    /**
     * This is a helper which lists all unread mails belonging to this mailbox.
     *
     * @param string $order A regular ordering constraint, consisting of a field name
     *     and the optional prefix 'reverse'. The default is 'reverse received'.
     * @return Array A list of found mails, or false on failure.
     */
    function list_unread_mails($order = 'reverse received')
    {
        $qb = $this->get_qb_mails();
        $qb->add_order($order);
        $qb->add_constraint('status', '=', NET_NEHMER_MAIL_STATUS_UNREAD);
        
        $results = $qb->execute();

        return $results;
    }
    
    /**
     * This is a helper which lists all mails with given status belonging to this mailbox.
     *
     * @param string $status A mail status code. See interface class to see all possible statuses.
     * @param string $order A regular ordering constraint, consisting of a field name
     *     and the optional prefix 'reverse'. The default is 'reverse received'.
     * @return Array A list of found mails, or false on failure.
     */
    function list_mails_with_status($status = NET_NEHMER_MAIL_STATUS_READ, $order = 'reverse received')
    {
        $qb = $this->get_qb_mails();
        $qb->add_order($order);
        $qb->add_constraint('status', '=', NET_NEHMER_MAIL_STATUS_STARRED);
        
        $results = $qb->execute();

        return $results;
    }

    /**
     * Returns the total message count within this mailbox. You do not need
     * read access for this query, as ACL is bypassed using the count_unchecked()
     * method of the MidCOM Query Builder. This way you will always get a fast and
     * accurate result (especially useful for quota checks).
     *
     * @return int Number of Messages in this Mailbox.
     */
    function get_message_count()
    {
        if ($this->_message_count == -1);
        {
            $qb = $this->get_qb_mails();
            $this->_message_count = $qb->count_unchecked();
        }
        return $this->_message_count;
    }

    /**
     * Returns the total number of unseen messages within this mailbox. Same notes
     * as with get_message_count() applies.
     *
     * This operation is cached.
     *
     * @return int Number of unseen Messages in this Mailbox.
     */
    function get_unseen_count()
    {
        if ($this->_unseen_count == -1)
        {
            $qb = $this->get_qb_mails();
            $qb->add_constraint('status', '=', NET_NEHMER_MAIL_STATUS_UNREAD);
            // $qb->add_constraint('mail.isread', '=', false);
            $this->_unseen_count = $qb->count_unchecked();
        }
        return $this->_unseen_count;
    }

    /**
     * Returns true, if this Mailbox has a qutoa set and the message count is
     * equal to or larger then the quota.
     *
     * It reimplements the message count getter, but without the ACL checks so
     * that other users can check quotas.
     *
     * Note, that this call does not take user rights into account. If the current
     * user has the ignore_quota priv, this call will still return true if the
     * user is over quota.
     *
     * @return bool Indicating Quota state.
     */
    function is_over_quota()
    {
        if ($this->quota == 0)
        {
            // No Quota set.
            return false;
        }

        $this->get_message_count();
        return ($this->_message_count >= $this->quota);
    }

    /**
     * This function delivers a mail to this mailbox. If the Quota of the mailbox
     * is set (nonzero) and would be exceeded by this mail delivery, the component
     * checks the ignore_quota privilege. If it is set, the mail can be delivered
     * nonetheless, otherwise delivery fails.
     *
     * The operation uses the creators initial owner privileges to reassign ownership
     * to the user we're sending to. Note, that this ownership is inherited from the
     * mailbox parent object, thus we have *no* ownership defined explicitly at the mail.
     *
     * This operation requires a valid user being logged on.
     *
     * @param midcom_core_user $sender The user sending the Mail.
     * @param string $subject The subject of the message.
     * @param string $body The message body.
     * @return mixed Returns message guid on success, or PEAR_Error on failure.
     */
    // function deliver_mail($sender, $subject, $body)
    // {
    //     debug_push_class(__CLASS__, __FUNCTION__);
    //     debug_add("delivering mail from {$sender->id}");
    //     
    //     if (   ! $_MIDCOM->auth->can_do('net.nehmer.mail:ignore_quota', $this)
    //         && $this->is_over_quota())
    //     {
    //         return $this->raiseError($_MIDCOM->i18n->get_string('mailbox full.'), NET_NEHMER_MAIL_ERROR_MAILBOXFULL);
    //     }
    //     if (! $_MIDCOM->auth->can_user_do('midgard:create', null, 'net_nehmer_mail_mail'))
    //     {
    //         return $this->raiseError($_MIDCOM->i18n->get_string('access denied', 'midcom'), NET_NEHMER_MAIL_ERROR_DENIED);
    //     }
    // 
    //     $mail = new net_nehmer_mail_mail();
    //     // $mail->mailbox = $this->guid;
    //     $mail->sender = $sender->guid;
    //     $mail->subject = $subject;
    //     $mail->body = $body;
    //     $mail->received = time();
    //     $mail->isread = false;
    // 
    //     if (! $mail->create())
    //     {
    //         // This should normally not fail, as the class default privilege is set accordingly.
    //         debug_push_class(__CLASS__, __FUNCTION__);
    //         debug_print_r('Mail object was:', $mail);
    //         $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a mail record. See the debug level log for details.');
    //         // This will exit.
    //     }
    // 
    //     $relation = new net_nehmer_mail_relation();
    //     $relation->mailbox = $this->id;
    //     $relation->mail = $mail->id;
    // 
    //     if (! $relation->create())
    //     {
    //         // This should normally not fail, as the class default privilege is set accordingly.
    //         debug_push_class(__CLASS__, __FUNCTION__);
    //         debug_print_r('Mailbox object was:', $this);
    //         debug_print_r('Mail object was:', $mail);
    //         debug_pop();
    //         $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a MailToMailbox relation record. See the debug level log for details.');
    //         // This will exit.
    //     }
    // 
    //     // We don't want an owner privilege here. Instead, we want to have only the read flag set.
    //     // We do this only if we have a valid user logged on. Otherwise, there won't be any privilege
    //     // to set.
    //     if ($_MIDCOM->auth->user !== null)
    //     {
    //         $mail->set_privilege('midgard:read');
    //         $mail->unset_privilege('midgard:owner');
    //     }
    //     
    //     debug_pop();
    //     return $mail->guid;
    // }

    /**
     * This function will automatically create the inbox for the currently active user.
     * This is called implicitly if the autocreate_inbox configuration option is set.
     * If the user may not create inboxes implicitly, SUDO privileges are requestet.
     *
     * Any error will trigger generate_error.
     *
     * @param midcom_core_user $user The user to look up, defaulting to the currently authenticated user.
     * @return net_nehmer_mail_mailbox The autocreated INBOX.
     */
    function autocreate_inbox($user = null)
    {
        $require_sudo = ! $_MIDCOM->auth->can_user_do('midgard:create', null, 'net_nehmer_mail_mailbox');

        if (   $require_sudo
            && ! $_MIDCOM->auth->request_sudo())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'net.nehmer.mail failed to acquire sudo privileges for automatic INBOX creation.');
            // This will exit.
        }

        if ($user === null)
        {
            $owner = $_MIDCOM->auth->user;
        }
        else
        {
            $owner = $user;
        }

        $config = $GLOBALS['midcom_component_data']['net.nehmer.mail']['config'];

        $mailbox = new net_nehmer_mail_mailbox();
        $mailbox->owner = $owner->guid;
        $mailbox->quota = $config->get('default_quota');
        $mailbox->name = 'INBOX';
        if (! $mailbox->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Last Midgard Error:', mgd_errstr());
            debug_print_r('Tried to create this object:', $mailbox);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to auto-create the INBOX of the current user. See the debug level log for details: ' . mgd_errstr());
            // This will exit.
        }

        // Unset owner privileges automatically created by DBA, this is inherited from the
        // person.
        $mailbox->unset_privilege('midgard:owner');

        if ($require_sudo)
        {
            $_MIDCOM->auth->drop_sudo();
        }

        return $mailbox;
    }

    /**
     * This function will automatically create the outbox for the currently active user.
     * This is called implicitly if the autocreate_inbox configuration option is set.
     * If the user may not create inboxes implicitly, SUDO privileges are requestet.
     *
     * Any error will trigger generate_error.
     *
     * @param midcom_core_user $user The user to look up, defaulting to the currently authenticated user.
     * @return net_nehmer_mail_mailbox The autocreated OUTBOX.
     */
    function autocreate_outbox($user = null)
    {
        $require_sudo = ! $_MIDCOM->auth->can_user_do('midgard:create', null, 'net_nehmer_mail_mailbox');

        if (   $require_sudo
            && ! $_MIDCOM->auth->request_sudo())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'net.nehmer.mail failed to acquire sudo privileges for automatic INBOX creation.');
            // This will exit.
        }

        if ($user === null)
        {
            $owner = $_MIDCOM->auth->user;
        }
        else
        {
            $owner = $user;
        }

        $config = $GLOBALS['midcom_component_data']['net.nehmer.mail']['config'];

        $mailbox = new net_nehmer_mail_mailbox();
        $mailbox->owner = $owner->guid;
        $mailbox->quota = $config->get('default_quota');
        $mailbox->name = 'OUTBOX';
        if (! $mailbox->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Last Midgard Error:', mgd_errstr());
            debug_print_r('Tried to create this object:', $mailbox);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to auto-create the INBOX of the current user. See the debug level log for details: ' . mgd_errstr());
            // This will exit.
        }

        // Unset owner privileges automatically created by DBA, this is inherited from the
        // person.
        $mailbox->unset_privilege('midgard:owner');

        if ($require_sudo)
        {
            $_MIDCOM->auth->drop_sudo();
        }

        return $mailbox;
    }

    /**
     * The delete event deletes all messages bound to this mailbox.
     */
    function _on_deleted()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $mails = $this->list_mails();
        foreach ($mails as $mail)
        {
            if (! $mail->delete())
            {
                debug_add ("Failed to delete Mail {$mail->id}, ignoring silently.", MIDCOM_LOG_WARN);
            }
        }
        debug_pop();

        parent::_on_deleted();
    }
}