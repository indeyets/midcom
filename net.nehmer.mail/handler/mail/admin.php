<?php
/**
 * @package net.nehmer.mail
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a Mail view handler class for net.nehmer.mail
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 *
 * @see midcom_baseclasses_components_handler
 * @package net.nehmer.mail
 */

class net_nehmer_mail_handler_mail_admin extends midcom_baseclasses_components_handler
{
    /**
     * Current mails mailbox
     *
     * @var net_nehmer_mail_mailbox
     * @access private
     */
    var $_mailbox = null;

    /**
     * Current mail
     *
     * @var net_nehmer_mail_mail
     * @access private
     */
    var $_mail = null;

    /**
     * List of deleted mails
     *
     * @var array of net_nehmer_mail_mail objects
     * @access private
     */
    var $_deleted_mails = array();

    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * _on_initialize is called by midcom on creation of the handler.
     */
    function _on_initialize()
    {
        $_MIDCOM->auth->require_valid_user();
    }

    function _populate_node_toolbar($handler_id)
    {
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data($handler_id)
    {
        $this->_request_data['mailbox'] =& $this->_mailbox;
        $this->_request_data['mail'] =& $this->_mail;

        if ($handler_id == 'mail-admin-trash')
        {
            $this->_request_data['deleted_mails'] =& $this->_deleted_mails;
            $this->_request_data['in_trash_view'] = true;
            $this->_request_data['mailbox_classname'] = "trash";
        }
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_trash($handler_id, $args, &$data)
    {
        $this->_deleted_mails = net_nehmer_mail_mail::list_deleted_mails();

        $this->_prepare_request_data($handler_id);

        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_perform($handler_id, $args, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $return_url = "{$prefix}mail/admin/trash.html";
        if (   isset($_REQUEST['return_url'])
            && !empty($_REQUEST['return_url']))
        {
            $return_url = $_REQUEST['return_url'];
        }

        // debug_print_r('perform request',$_REQUEST);

        $messages = array();
        if (   array_key_exists('selections', $_REQUEST)
            && is_array($_REQUEST['selections']))
        {
            switch($_REQUEST['net_nehmer_mail_actions'])
            {
                case 'net_nehmer_mail_action_restore':
                case 'net_nehmer_mail_action_purge':
                    $qb = new midgard_query_builder('net_nehmer_mail_mail');
                    $qb->add_constraint('id', 'IN', $_REQUEST['selections']);
                    $qb->include_deleted();

                    $mails = $qb->execute();
                    foreach ($mails as $mail)
                    {
                        $messages[$mail->id] =& $mail;
                    }
                    break;
                default:
                    foreach ($_REQUEST['selections'] as $msg_id)
                    {
                        $mail = new net_nehmer_mail_mail($msg_id);
                        if (! $mail)
                        {
                            debug_push_class(__CLASS__, __FUNCTION__);
                            debug_add("The message id {$msg_id} is invalid (or we have insufficient permissions), skipping.", MIDCOM_LOG_INFO);
                            debug_pop();
                            continue;
                        }
                        $messages[$msg_id] = $mail;
                    }
            }
        }

        if (   !array_key_exists('net_nehmer_mail_actions', $_REQUEST)
            || (   !$messages
                    && !array_key_exists('net_nehmer_mail_action_empty_trash', $_REQUEST) ))
        {
            // Nothing to do, no messages selected.
            $_MIDCOM->relocate($return_url);
            // This will exit.
        }

        if ($_REQUEST['net_nehmer_mail_actions'] == 'net_nehmer_mail_action_delete')
        {
            foreach ($messages as $msg_id => $mail)
            {
                if (! $_MIDCOM->auth->can_do('midgard:delete', $mail))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Insufficient privileges on message id {$msg_id}, skipping.", MIDCOM_LOG_INFO);
                    debug_pop();
                    $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('failed to delete mail'), 'warning');
                    continue;
                }
                if (! $mail->delete())
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Failed to delete message id {$msg_id}, last Midgard error was: " . mgd_errstr(), MIDCOM_LOG_INFO);
                    debug_pop();
                    $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('failed to delete mail'), 'warning');
                }
            }

            $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('successfully deleted selected mails'), 'ok');
        }

        if ($_REQUEST['net_nehmer_mail_actions'] == 'net_nehmer_mail_action_mark_as_starred')
        {
            foreach ($messages as $msg_id => $mail)
            {
                if (! $_MIDCOM->auth->can_do('midgard:update', $mail))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Insufficient privileges on message id {$msg_id}, skipping.", MIDCOM_LOG_INFO);
                    debug_pop();
                    $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('failed to change status'), 'warning');
                    continue;
                }
                if (! $mail->set_status(NET_NEHMER_MAIL_STATUS_STARRED))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Failed to change status for message id {$msg_id}, last Midgard error was: " . mgd_errstr(), MIDCOM_LOG_INFO);
                    debug_pop();
                    $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('failed to change status'), 'warning');
                }
            }

            $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('successfully updated selected mails'), 'ok');
        }

        if ($_REQUEST['net_nehmer_mail_actions'] == 'net_nehmer_mail_action_mark_as_unread')
        {
            foreach ($messages as $msg_id => $mail)
            {
                if (! $_MIDCOM->auth->can_do('midgard:update', $mail))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Insufficient privileges on message id {$msg_id}, skipping.", MIDCOM_LOG_INFO);
                    debug_pop();
                    $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('failed to change status'), 'warning');
                    continue;
                }
                if (! $mail->set_status(NET_NEHMER_MAIL_STATUS_UNREAD))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Failed to change status for message id {$msg_id}, last Midgard error was: " . mgd_errstr(), MIDCOM_LOG_INFO);
                    debug_pop();
                    $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('failed to change status'), 'warning');
                }
            }

            $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('successfully updated selected mails'), 'ok');
        }

        if ($_REQUEST['net_nehmer_mail_actions'] == 'net_nehmer_mail_action_purge')
        {
            foreach ($messages as $msg_id => $mail)
            {
                if (! $mail->purge())
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Failed to purge deleted mail id {$mail->id}, last Midgard error was: " . mgd_errstr(), MIDCOM_LOG_INFO);
                    debug_pop();
                    $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('failed to purge mail'), 'warning');
                }
            }

            $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('successfully deleted selected mails'), 'ok');
        }

        if ($_REQUEST['net_nehmer_mail_actions'] == 'net_nehmer_mail_action_restore')
        {
            foreach ($messages as $msg_id => $mail)
            {
                if (! net_nehmer_mail_mail::undelete($mail->guid))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Failed to restore deleted mail id {$mail->id}, last Midgard error was: " . mgd_errstr(), MIDCOM_LOG_INFO);
                    debug_pop();
                    $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('failed to restore mail'), 'warning');
                }
            }

            $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('successfully restored selected mails'), 'ok');
        }

        if (array_key_exists('net_nehmer_mail_action_empty_trash', $_REQUEST))
        {
            $this->_deleted_mails = net_nehmer_mail_mail::list_deleted_mails();
            foreach ($this->_deleted_mails as $mail)
            {
                if (! $mail->purge())
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Failed to purge deleted mail id {$mail->id}, last Midgard error was: " . mgd_errstr(), MIDCOM_LOG_INFO);
                    debug_pop();
                    $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('failed to purge mail'), 'warning');
                }
            }

            $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('successfully emptied trash'), 'ok');
        }

        $_MIDCOM->relocate($return_url);

        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_restore($handler_id, $args, &$data)
    {
        if (! isset($args[0]))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Wrong parameter count.");
            // This will exit.
        }

        $qb = new midgard_query_builder('net_nehmer_mail_mail');
        $qb->add_constraint('guid', '=', $args[0]);
        $qb->include_deleted();

        $results = $qb->execute();

        if (count($results) == 0)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Couldn't find mail: {$args[0]}.");
            // This will exit.
        }

        $mail =& $results[0];
        if (! net_nehmer_mail_mail::undelete($mail->guid))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to restore deleted mail id {$mail->id}, last Midgard error was: " . mgd_errstr(), MIDCOM_LOG_INFO);
            debug_pop();
            $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('failed to restore mail'), 'warning');
        }
        else
        {
            $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('mail restored successfully'), 'ok');
        }

        $_MIDCOM->relocate("");

        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        if (! isset($args[0]))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Didn't get mail guid as parameter.");
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Invalid parameters given! See debug log for details.");
            // This will exit
        }

        $return_url = "";

        if (isset($_REQUEST['return_url']))
        {
            $return_url = $_REQUEST['return_url'];
        }

        $this->_mail = new net_nehmer_mail_mail($args[0]);
        $this->_mailbox =& $this->_mail->get_mailbox();

        if (! $this->_mail)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Couldn't find mail: {$args[0]}.");
            // This will exit.
        }

        if (! $_MIDCOM->auth->can_do('midgard:delete', $this->_mail))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Insufficient privileges on mail id {$this->_mail->id}.", MIDCOM_LOG_INFO);
            debug_pop();
            $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('failed to delete mail'), 'warning');
            $_MIDCOM->relocate($return_url);
        }

        if (! $this->_mail->delete())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to delete mail id {$this->_mail->id}, last Midgard error was: " . mgd_errstr(), MIDCOM_LOG_INFO);
            debug_pop();
            $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('failed to delete mail'), 'warning');
        }
        else
        {
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            $undelete_url = "{$prefix}mail/admin/restore/{$this->_mail->guid}.html";

            $delete_msg = $data['l10n']->get('mail deleted successfully');
            $delete_msg .= "<br /><a href=\"{$undelete_url}\">" . $this->_l10n->get('undelete') . "?</a>";

            $_MIDCOM->uimessages->add(
                $data['l10n']->get('net.nehmer.mail'),
                $delete_msg,
                'ok'
            );
        }

        $_MIDCOM->relocate($return_url);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_trash($handler_id, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        $data['return_url'] = "{$prefix}mail/admin/trash.html";
        $data['action_handler_url'] = "{$prefix}mail/admin/perform.html";
        $data['perform_button_name'] = 'net_nehmer_mail_action_perform';

        $data['empty_trash_button_name'] = 'net_nehmer_mail_action_empty_trash';

        $data['actions'] = array(
            'net_nehmer_mail_action_purge' => $this->_l10n_midcom->get('purge delete'),
            'net_nehmer_mail_action_restore' => $this->_l10n->get('undelete'),
        );

        midcom_show_style('trash-items-start');

        if (count($this->_deleted_mails) == 0)
        {
            midcom_show_style('trash-items-empty');
        }
        else
        {
            foreach ($this->_deleted_mails as $i => $mail)
            {
                if ($mail->status == NET_NEHMER_MAIL_STATUS_SENT)
                {
                    continue;
                }

                $data['mail'] =& $mail;
                $data['row_class'] = 'even';
                if ($i % 2 == 0)
                {
                    $data['row_class'] = 'odd';
                }
                $data['sender'] =& $_MIDCOM->auth->get_user($mail->sender);

                midcom_show_style('trash-items-item');
            }
        }

        midcom_show_style('trash-items-end');
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_perform($handler_id, &$data)
    {
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_restore($handler_id, &$data)
    {
    }
}

?>