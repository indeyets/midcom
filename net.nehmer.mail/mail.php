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
class net_nehmer_mail_mail extends __net_nehmer_mail_mail
{
    function net_nehmer_mail_mail($id = null)
    {
        parent::__net_nehmer_mail_mail($id);
    }

    /**
     * This is a small helper which prepares a query builder ready to query the mailboxes
     * this mail belongs to
     *
     * @return midcom_core_querybuilder The prepared Querybuilder.
     */
    function get_qb_mailboxes()
    {
        $qb = net_nehmer_mail_relation::new_query_builder();
        $qb->add_constraint('mail', '=', $this->id);
        
        return $qb;
    }

    /**
     * The get_parent_guid_uncached method links to the owning mailbox. If the mailbox cannot be resolved,
     * the error is logged but ignored silently, to allow for error handling.
     */
    function get_parent_guid_uncached()
    {
        $mailbox_guid = false;
        
        $mailbox = $this->get_mailbox();
        
        if (! $mailbox)
        {
            $mailbox_guid = net_nehmer_mail_mailbox::get_outbox();
        }
        else
        {
            $mailbox_guid = $mailbox->guid;            
        }
        
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
            return false;
        }
        
        $qb = $this->get_qb_mailboxes();
        $qb->add_constraint('mailbox.owner', '=', $_MIDCOM->auth->user->guid);
        $results = $qb->execute();
        
        if (count($results) < 1)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("No mailbox founded for mail {$this->id} with owner {$_MIDCOM->auth->user->guid}.");
            debug_pop();
            return false;
        }

        return $results[0]->get_mailbox();
    }
    
    /**
     * Returns an instance of the mailboxes that has this message.
     *
     * @return Array of net_nehmer_mail_mailbox objects or empty array on failure
     */
    function get_other_mailboxes()
    {
        $qb = $this->get_qb_mailboxes();
        $qb->add_constraint('mailbox.owner', '<>', $_MIDCOM->auth->user->guid);
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
    
    function get_receivers($mail_id)
    {
        //TODO: Implement
        return array();
    }
    
    function deliver_to(&$receivers)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        debug_print_r('Receivers: ',$receivers);
        debug_pop();
        
        foreach ($receivers as $k => $receiver)
        {
            $inbox = net_nehmer_mail_mailbox::get_inbox($receiver);
            
            $relation = new net_nehmer_mail_relation();
            $relation->mailbox = $inbox->id;
            $relation->mail = $this->id;

            $_MIDCOM->auth->request_sudo();

            if (! $relation->create())
            {
                // This should normally not fail, as the class default privilege is set accordingly.
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_print_r('Mailbox object was:', $inbox);
                debug_print_r('Mail object was:', $this);
                debug_pop();
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a MailToMailbox relation record. See the debug level log for details.');
                // This will exit.
            }
            
            $_MIDCOM->auth->drop_sudo();
        }
        
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
            
            $relation = new net_nehmer_mail_relation();
            $relation->mailbox = $outbox->id;
            $relation->mail = $this->id;
            
            $_MIDCOM->auth->request_sudo();

            if (! $relation->create())
            {
                // This should normally not fail, as the class default privilege is set accordingly.
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_print_r('Mailbox object was:', $outbox);
                debug_print_r('Mail object was:', $this);
                debug_pop();
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a MailToMailbox relation record. See the debug level log for details.');
                // This will exit.
            }

            $_MIDCOM->auth->drop_sudo();
        }
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