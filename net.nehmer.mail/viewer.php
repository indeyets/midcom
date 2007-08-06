<?php
/**
 * @package net.nehmer.mail
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * On-Site Mailing System site interface class
 *
 *
 * @package net.nehmer.mail
 */

class net_nehmer_mail_viewer extends midcom_baseclasses_components_request
{
    function net_nehmer_mail_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
    }

    function _on_initialize()
    {   
        // Match: /
        $this->_request_switch['mailbox-view-index'] = Array
        (
            'handler' => Array('net_nehmer_mail_handler_mailbox_view', 'view'),
        );
        
        // Match: mailbox/view/<guid>
        $this->_request_switch['mailbox-view'] = Array
        (
            'handler' => Array('net_nehmer_mail_handler_mailbox_view', 'view'),
            'fixed_args' => Array('mailbox','view'),
            'variable_args' => 1,
        );
        
        // Match: mail/live_preview
        $this->_request_switch['mail-live-preview'] = Array
        (
            'handler' => Array('net_nehmer_mail_handler_mail_view', 'live_preview'),
            'fixed_args' => Array('mail','live_preview'),
        );
        // Match: mail/view/<guid>
        $this->_request_switch['mail-view'] = Array
        (
            'handler' => Array('net_nehmer_mail_handler_mail_view', 'view'),
            'fixed_args' => Array('mail','view'),
            'variable_args' => 1,
        );
        // Match: mail/admin/trash
        $this->_request_switch['mail-admin-trash'] = Array
        (
            'handler' => Array('net_nehmer_mail_handler_mail_admin', 'trash'),
            'fixed_args' => Array('mail','admin','trash'),
        );
        // Match: mail/admin/perform
        $this->_request_switch['mail-admin-perform'] = Array
        (
            'handler' => Array('net_nehmer_mail_handler_mail_admin', 'perform'),
            'fixed_args' => Array('mail','admin','perform'),
        );
        // Match: mail/admin/delete/<guid>
        $this->_request_switch['mail-admin-delete-one'] = Array
        (
            'handler' => Array('net_nehmer_mail_handler_mail_admin', 'delete'),
            'fixed_args' => Array('mail','admin','delete'),
            'variable_args' => 1,
        );
        // Match: mail/admin/restore/<guid>
        $this->_request_switch['mail-admin-restore'] = Array
        (
            'handler' => Array('net_nehmer_mail_handler_mail_admin', 'restore'),
            'fixed_args' => Array('mail','admin','restore'),
            'variable_args' => 1,
        );
        
        // Match: mail/compose/new
        $this->_request_switch['mail-compose-new'] = Array
        (
            'handler' => Array('net_nehmer_mail_handler_mail_compose', 'create'),
            'fixed_args' => Array('mail','compose','new'),
        );
        // Match: mail/compose/new/<guid>
        $this->_request_switch['mail-compose-new-quick'] = Array
        (
            'handler' => Array('net_nehmer_mail_handler_mail_compose', 'create'),
            'fixed_args' => Array('mail','compose','new'),
            'variable_args' => 1,
        );
        // Match: mail/compose/reply/<guid>
        $this->_request_switch['mail-compose-reply'] = Array
        (
            'handler' => Array('net_nehmer_mail_handler_mail_compose', 'create'),
            'fixed_args' => Array('mail','compose','reply'),
            'variable_args' => 1,
        );
        // Match: mail/compose/replyall/<guid>
        $this->_request_switch['mail-compose-reply-all'] = Array
        (
            'handler' => Array('net_nehmer_mail_handler_mail_compose', 'create'),
            'fixed_args' => Array('mail','compose','replyall'),
            'variable_args' => 1,
        );
        
        // Match: admin
        $this->_request_switch['admin-welcome'] = Array
        (
            'handler' => Array('net_nehmer_mail_handler_mailbox_admin', 'welcome'),
            'fixed_args' => Array('admin'),
        );
        // Match: admin/create
        $this->_request_switch['admin-create'] = Array
        (
            'handler' => Array('net_nehmer_mail_handler_mailbox_admin', 'create'),
            'fixed_args' => Array('admin','create'),
        );
        // Match: admin/edit/<guid>
        $this->_request_switch['admin-edit'] = Array
        (
            'handler' => Array('net_nehmer_mail_handler_mailbox_admin', 'edit'),
            'fixed_args' => Array('admin','edit'),
            'variable_args' => 1,
        );
        // Match: admin/delete/<guid>
        $this->_request_switch['admin-delete'] = Array
        (
            'handler' => Array('net_nehmer_mail_handler_mailbox_admin', 'delete'),
            'fixed_args' => Array('admin','delete'),
            'variable_args' => 1,
        );
        // Match: admin/ajax/delete/<guid>
        $this->_request_switch['admin-ajax-delete'] = Array
        (
            'handler' => Array('net_nehmer_mail_handler_mailbox_admin', 'delete'),
            'fixed_args' => Array('admin','ajax','delete'),
            'variable_args' => 1,
        );
    
        $this->_request_switch[] = Array
        (
            'handler' => 'mail_show',
            'fixed_args' => Array('mail', 'show'),
            'variable_args' => 1,
        );

        $this->_request_switch[] = Array
        (
            'handler' => 'mail_searchto',
            'fixed_args' => Array('mail', 'new'),
        );
        $this->_request_switch['new'] = Array
        (
            'handler' => 'mail_new',
            'fixed_args' => Array('mail', 'new'),
            'variable_args' => 1,
        );
        $this->_request_switch['reply'] = Array
        (
            'handler' => 'mail_new',
            'fixed_args' => Array('mail', 'reply'),
            'variable_args' => 1,
        );

        $this->_request_switch[] = Array
        (
            'handler' => 'mail_sent',
            'fixed_args' => Array('mail', 'sent'),
            'variable_args' => 1,
        );

        $this->_request_switch[] = Array
        (
            'handler' => 'mail_manage',
            'fixed_args' => Array('mail', 'manage'),
        );
        
        // Match: config/
        $this->_request_switch['config'] = Array
        (
            'handler' => Array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/net/nehmer/mail/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );        

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/net.nehmer.mail/styles/main.css",
            )
        );
        
        $_MIDCOM->enable_jquery();
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/net.nehmer.mail/js/main.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/net.nehmer.mail/js/jquery.livePreview.js');
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/net.nehmer.mail/js/showdown.pack.js');
    }
    
    function _on_can_handle()
    {
        return true;
    }

    /**
     * Generic request startup work:
     *
     * - Load the Schema Database
     * - Populate the Node Toolbar
     */
    function _on_handle($handler, $args)
    {
        // $this->_request_data['schemadb'] =
        //     midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
        // 
        // $this->_add_categories();
        
        $this->_request_data['in_trash_view'] = false;
        $this->_request_data['in_compose_view'] = false;
        $this->_request_data['mailboxes'] =& net_nehmer_mail_mailbox::list_mailboxes();
        
        $this->_populate_node_toolbar();

        return true;
    }
    
    /**
     * Populates the node toolbar depending on the users rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {
        // if ($this->_content_topic->can_do('midgard:create'))
        // {
        //     foreach (array_keys($this->_request_data['schemadb']) as $name)
        //     {
        //         $this->_node_toolbar->add_item(Array(
        //             MIDCOM_TOOLBAR_URL => "create/{$name}.html",
        //             MIDCOM_TOOLBAR_LABEL => sprintf
        //             (
        //                 $this->_l10n_midcom->get('create %s'),
        //                 $this->_l10n->get($this->_request_data['schemadb'][$name]->description)
        //             ),
        //             MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
        //             MIDCOM_TOOLBAR_ACCESSKEY => 'n',
        //         ));
        //     }
        // }

        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => 'config.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            ));
        }
    }

    /**
     * Prepares the mail for showing.
     */
    function _handler_mail_show($handler_id, $args, &$data)
    {
        $data['mail'] = new net_nehmer_mail_mail($args[0]);
        if (! $data['mail'])
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = 'E-Mail ID unknown.';
            return false;
        }

        if (! $data['mail']->isread)
        {
            $data['mail']->isread = true;
            if (! $data['mail']->update())
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Warning, we could not change the read state of message {$data['mail']->guid}. Ignoring silently.",
                    MIDCOM_LOG_WARN);
                debug_print_r('Mail was:', $data['mail']);
                debug_pop();
            }
        }

        $data['mailbox'] = $data['mail']->get_mailbox();
        if (! $data['mailbox'])
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = 'Mailbox unknown.';
            return false;
        }

        if ($data['mailbox']->name == 'INBOX')
        {
            $data['outbox_mode'] = false;
            $data['name_translated'] = $this->_l10n->get('inbox');
        }
        else if ($data['mailbox']->name == 'OUTBOX')
        {
            $data['outbox_mode'] = true;
            $data['name_translated'] = $this->_l10n->get('outbox');
        }
        else
        {
            $data['outbox_mode'] = false;
            $data['name_translated'] = $data['mailbox']->name;
        }

        $this->_component_data['active_leaf'] = $this->_request_data['mailbox']->guid;
        $_MIDCOM->set_pagetitle($this->_request_data['name_translated']);
        $_MIDCOM->set_26_request_metadata(time(), $this->_request_data['mail']->guid);
        $tmp = Array
        (
            Array
            (
                MIDCOM_NAV_URL => "mail/show/{$data['mail']->guid}.html",
                MIDCOM_NAV_NAME => $data['mail']->subject,
            ),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     * Deletes a mail and relocates accordingly. This call doesn't have a show hook, as it will
     * relocate or generate_error in all branches.
     */
    function _handler_mail_manage($handler_id, $args, &$data)
    {
        if ($_REQUEST['return_url'])
        {
            $return_url = $_REQUEST['return_url'];
        }
        else
        {
            $return_url = '';
        }

        $messages = Array();
        if (   array_key_exists('msg_ids', $_REQUEST)
            && is_array($_REQUEST['msg_ids']))
        {
            foreach ($_REQUEST['msg_ids'] as $msg_id)
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

        if (! $messages)
        {
            // Nothing to do, no messages selected.
            $_MIDCOM->relocate($return_url);
            // This will exit.
        }

        if (array_key_exists('net_nehmer_mail_mail_db', $_REQUEST))
        {
            foreach ($messages as $msg_id => $mail)
            {
                if (! $_MIDCOM->auth->can_do('midgard:delete', $mail))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Insufficient privileges on message id {$msg_id}, skipping.", MIDCOM_LOG_INFO);
                    debug_pop();
                    continue;
                }
                if (! $mail->delete())
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Failed to delete message id {$msg_id}, last Midgard error was: " . mgd_errstr(), MIDCOM_LOG_INFO);
                    debug_pop();
                }
            }
        }

        $_MIDCOM->relocate($return_url);
        // This will exit.
    }

    /**
     * Displays an e-Mail
     */
    function _show_mail_show($handler_id, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        if ($data['mailbox']->name == 'INBOX')
        {
            $data['return_url'] = "{$prefix}mailbox/INBOX.html";
        }
        else if ($data['mailbox']->name == 'OUTBOX')
        {
            $data['return_url'] = "{$prefix}mailbox/OUTBOX.html";
        }
        else
        {
            $data['return_url'] = "{$prefix}mailbox/{$data['mailbox']->guid}.html";
        }
        $data['sender'] =& $_MIDCOM->auth->get_user($data['mail']->sender);
        $data['newmail_url'] = "{$prefix}mail/new/{$data['mail']->sender}.html";
        $data['reply_url'] = "{$prefix}mail/reply/{$data['mail']->guid}.html";
        $data['body_formatted'] = $data['mail']->get_body_formatted();

        $data['manage_url'] = "{$prefix}mail/manage.html";
        $data['delete_submit_button_name'] = 'net_nehmer_mail_mail_db';
        $data['can_delete'] = $_MIDCOM->auth->can_do('midgard:delete', $data['mail']);

        midcom_show_style('mail-show');
    }

    /**
     * The index handler will list all mailboxes of the current user, along with their message
     * counts. The handler only prepares the mailbox listing, the viewer code takes the bulk of
     * the logic here.
     */
    function _handler_index ($handler_id, $args, &$data)
    {
        if ($_MIDCOM->auth->user === null)
        {
            $data['mailboxes'] = Array();
            return true;
        }

        $data['mailboxes'] = net_nehmer_mail_mailbox::list_mailboxes();

        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);

        return true;
    }

    /**
     * The rendering code consists of a standard init/loop/end construct.
     */
    function _show_index($handler_id, &$data)
    {
        midcom_show_style('mailbox-list-heading');

        if (count($data['mailboxes']) == 0)
        {
            midcom_show_style('mailbox-list-none-found');
        }
        else
        {
            midcom_show_style('mailbox-list-start');

            $shaded_background = true;

            if (array_key_exists('INBOX', $data['mailboxes']))
            {
                $data['mailbox'] = $data['mailboxes']['INBOX'];
                $this->_show_index_prepare_requestdata($data);

                $data['background_class'] = ($shaded_background) ? 'shaded' : 'not_shaded';
                $shaded_background = ! $shaded_background;
                midcom_show_style('mailbox-list-item');
            }
            if (array_key_exists('OUTBOX', $data['mailboxes']))
            {
                $data['mailbox'] = $data['mailboxes']['OUTBOX'];
                $this->_show_index_prepare_requestdata($data);

                $data['background_class'] = ($shaded_background) ? 'shaded' : 'not_shaded';
                $shaded_background = ! $shaded_background;
                midcom_show_style('mailbox-list-item');
            }

            foreach ($data['mailboxes'] as $name => $mailbox)
            {
                if (   $name == 'INBOX'
                    || $name == 'OUTBOX')
                {
                    continue;
                }
                $data['mailbox'] = $data['mailboxes'][$name];
                $this->_show_index_prepare_requestdata($data);

                $data['background_class'] = ($shaded_background) ? 'shaded' : 'not_shaded';
                $shaded_background = ! $shaded_background;
                midcom_show_style('mailbox-list-item');
            }

            midcom_show_style('mailbox-list-end');
        }
    }

    /**
     * Helper function used in _show_index to prepare all meta-information around the mailbox.
     */
    function _show_index_prepare_requestdata(&$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        if ($data['mailbox']->name == 'INBOX')
        {
            $data['name_translated'] = $this->_l10n->get('inbox');
            $data['url'] = "{$prefix}mailbox/INBOX.html";
        }
        else if ($data['mailbox']->name == 'OUTBOX')
        {
            $data['name_translated'] = $this->_l10n->get('outbox');
            $data['url'] = "{$prefix}mailbox/OUTBOX.html";
        }
        else
        {
            $data['name_translated'] = $data['mailbox']->name;
            $data['url'] = "{$prefix}mailbox/{$data['mailbox']->guid}.html";
        }

        $data['msgcount'] = $data['mailbox']->get_message_count();
        $data['unread'] = $data['mailbox']->get_unseen_count();

        if (   $data['unread'] > 0
            && $data['mailbox']->name != 'OUTBOX')
        {
            if ($data['unread'] == 1)
            {
                $data['unread_string'] = $this->_l10n->get('1 unread');
            }
            else
            {
                $data['unread_string'] = sprintf($this->_l10n->get('%d unread'), $data['unread']);
            }
        }
        else
        {
            $data['unread_string'] = '';
        }

        if ($data['mailbox']->quota > 0)
        {
            $data['count_string'] = "{$data['msgcount']}/{$data['mailbox']->quota}";
        }
        else
        {
            $data['count_string'] = $data['msgcount'];
        }
    }

    /**
     * Loads the mailbox referenced by $args, validats the privileges, and retrieves
     * all mails from that mailbox.
     */
    function _handler_mailbox($handler_id, $args, &$data)
    {
        if ($args[0] == 'INBOX')
        {
            $data['mailbox'] = net_nehmer_mail_mailbox::get_inbox();
        }
        else if ($args[0] == 'OUTBOX')
        {
            $data['mailbox'] = net_nehmer_mail_mailbox::get_outbox();
        }
        else
        {
            $data['mailbox'] = new net_nehmer_mail_mailbox($args[0]);
        }
        if (! $data['mailbox'])
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "This mailbox does not exist.";
            return false;
        }

        if ($data['mailbox']->name == 'INBOX')
        {
            $data['name_translated'] = $this->_l10n->get('inbox');
            $data['return_url'] = 'mailbox/INBOX.html';
            $data['outbox_mode'] = false;
        }
        else if ($data['mailbox']->name == 'OUTBOX')
        {
            $data['name_translated'] = $this->_l10n->get('outbox');
            $data['return_url'] = 'mailbox/OUTBOX.html';
            $data['outbox_mode'] = true;
        }
        else
        {
            $data['return_url'] = "mailbox/{$data['mailbox']->guid}.html";
            $data['name_translated'] = $data['mailbox']->name;
            $data['outbox_mode'] = false;
        }

        $this->_component_data['active_leaf'] = $this->_request_data['mailbox']->guid;
        $_MIDCOM->set_pagetitle($this->_request_data['name_translated']);
        $_MIDCOM->set_26_request_metadata(time(), $this->_request_data['mailbox']->guid);

        $_MIDCOM->auth->require_do('net.nehmer.mail:list_mails', $data['mailbox']);
        $data['mails'] = $data['mailbox']->list_mails();

        return true;
    }

    /**
     * Renders a mailbox' contents.
     */
    function _show_mailbox($handler_id, &$data)
    {
        midcom_show_style('mail-list-heading');

        if (count($data['mails']) == 0)
        {
            midcom_show_style('mail-list-none-found');
        }
        else
        {
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            $data['form_action'] = "{$prefix}mail/manage.html";
            midcom_show_style('mail-list-start');

            $shaded_background = true;

            foreach ($data['mails'] as $mail)
            {
                $data['mail'] = $mail;
                $data['background_class'] = ($shaded_background) ? 'shaded' : 'not_shaded';
                $data['url'] = "{$prefix}mail/show/{$mail->guid}.html";
                $data['sender'] =& $_MIDCOM->auth->get_user($mail->sender);
                $data['newmail_url'] = "{$prefix}mail/new/{$mail->sender}.html";
                $data['reply_url'] = "{$prefix}mail/reply/{$mail->guid}.html";

                $shaded_background = ! $shaded_background;
                midcom_show_style('mail-list-item');
            }

            $data['delete_submit_button_name'] = 'net_nehmer_mail_mail_db';
            midcom_show_style('mail-list-end');
        }
    }

    /**
     * Simple search form to allow lookup of users to write mails to.
     */
    function _handler_mail_searchto($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_user_do('midgard:create', null, 'net_nehmer_mail_mail');

        $data['search_string'] = '';
        $data['processing_msg'] = '';
        $data['results'] = null;

        if (array_key_exists('net_nehmer_mail_searchto_submit', $_REQUEST))
        {
            // Validate arguments and perform search
            if (! array_key_exists('search_string', $_REQUEST))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Invalid request, key search_string is missing.');
                // This will exit.
            }
            $data['search_string'] = trim($_REQUEST['search_string']);
            if (strlen($data['search_string']) < 3)
            {
                $data['processing_msg'] = $this->_l10n->get('please enter at least three characters.');
            }
            else
            {
                $qb = midcom_db_person::new_query_builder();
                $qb->begin_group('OR');
                $qb->add_constraint('username', 'LIKE', "%{$data['search_string']}%");
                $qb->add_constraint('firstname', 'LIKE', "%{$data['search_string']}%");
                $qb->add_constraint('lastname', 'LIKE', "%{$data['search_string']}%");
                $qb->end_group();
                $qb->add_constraint('password', '<>', '');
                $qb->add_constraint('id', '<>', 1); // This should actually skip SG0, but this way we at least catch the admin account.
                $qb->add_order('username');
                $data['results'] = $qb->execute();
                $data['result_count'] = $qb->count;
            }
        }

        $tmp = Array
        (
            Array
            (
                MIDCOM_NAV_URL => "mail/new.html",
                MIDCOM_NAV_NAME => $this->_l10n->get('lookup username'),
            ),
        );
        $this->_component_data['active_leaf'] = NET_NEHMER_MAIL_LEAFID_NEW;
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        $_MIDCOM->set_pagetitle($this->_l10n->get('lookup username'));

        return true;
    }

    /**
     * Shows the search form and, if applicable, the search results.
     */
    function _show_mail_searchto($handler_id, &$data)
    {
        midcom_show_style('mail-searchto-form');
        if ($data['results'] !== null)
        {
            midcom_show_style('mail-searchto-result-start');
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            foreach ($data['results'] as $key => $result)
            {
                $data['url'] = "{$prefix}mail/new/{$result->guid}.html";
                $data['username'] = $result->username;
                $data['name'] = $result->name;
                $data['guid'] = $result->guid;
                $data['person'] = $result;
                midcom_show_style('mail-searchto-result-item');
            }
            midcom_show_style('mail-searchto-result-end');
        }
    }

    /**
     * Prepares a datamanager for entering the contents of a new mail.
     *
     * The URL to which the component returns to after sending the mail is configurable
     * using these HTTP GET/POST parameters:
     *
     * - string net_nehmer_mail_return_to: If set, the content of this field is used as
     *   return_to request data key when showing the mail-sent screen. The component does
     *   not relocate to that URL it just mades it available for the style to show a
     *   corresponding link. It is strongly recommended to use only absolute or fully
     *   qualified URLs at this point.
     * - string net_nehmer_mail_relocate_to: If set, this actually overrides the relocate
     *   after sending the Mail (thus the component-built-in mails sent screen is never
     *   shown). The value shown here must be a valid argument for use with
     *   midcom_application::relocate().
     *
     * The return_to URL is always appended as GET Parameter to the URL relocated to
     * after sending again using the variable name net_nehmer_mail_return_to. The two
     * arguments can thus be combined.
     *
     * If return to is set, the default style will show an back link on top of the page.
     */
    function _handler_mail_new($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_user_do('midgard:create', null, 'net_nehmer_mail_mail');

        if (array_key_exists('net_nehmer_mail_return_to', $_REQUEST))
        {
            $data['return_to'] = $_REQUEST['net_nehmer_mail_return_to'];
        }
        else
        {
            $data['return_to'] = null;
        }

        if (array_key_exists('net_nehmer_mail_relocate_to', $_REQUEST))
        {
            $data['relocate_to'] = $_REQUEST['net_nehmer_mail_relocate_to'];
        }
        else
        {
            $data['relocate_to'] = null;
        }

        if ($handler_id == 'new')
        {
            $data['original_mail'] = null;
            $data['receiver'] =& $_MIDCOM->auth->get_user($args[0]);
            if (! $data['receiver'])
            {
                $this->errcode = MIDCOM_ERRNOTFOUND;
                $this->errstr = "The user {$args[0]} was not found.";
                return false;
            }
            $data['heading'] = sprintf($this->_l10n->get('write mail to %s:'), $data['receiver']->name);
        }
        else
        {
            $data['original_mail'] = new net_nehmer_mail_mail($args[0]);
            if (! $data['original_mail'])
            {
                $this->errcode = MIDCOM_ERRNOTFOUND;
                $this->errstr = "The mail {$args[0]} was not found.";
                return false;
            }
            $data['receiver'] =& $_MIDCOM->auth->get_user($data['original_mail']->sender);
            if (! $data['receiver'])
            {
                $this->errcode = MIDCOM_ERRNOTFOUND;
                $this->errstr = "The mail {$args[0]} had an unknown sender.";
                return false;
            }
            $data['heading'] = sprintf($this->_l10n->get('write reply to %s:'), $data['receiver']->name);
        }

        $data['controller'] =& midcom_helper_datamanager2_controller::create('nullstorage');
        $data['controller']->load_schemadb('file:/net/nehmer/mail/config/schemadb.inc');
        $data['controller']->schemaname = 'newmail';
        $data['controller']->defaults = Array('subject' => '', 'body' => '');
        $data['controller']->initialize();

        $data['formmanager'] =& $data['controller']->formmanager;
        $data['datamanager'] =& $data['controller']->datamanager;

        if ($data['return_to'] !== null)
        {
            $data['formmanager']->form->addElement('hidden', 'net_nehmer_mail_return_to', $data['return_to']);
        }
        if ($data['relocate_to'] !== null)
        {
            $data['formmanager']->form->addElement('hidden', 'net_nehmer_mail_relocate_to', $data['relocate_to']);
        }

        $data['receiver_mailbox'] = net_nehmer_mail_mailbox::get_inbox($data['receiver']);
        $data['error'] = null;

        // No other button in the form.
        if ($data['controller']->process_form() == 'save')
        {
            $this->_send_mail($data);
           // This will exit unless there is an error.
        }

        if ($handler_id == 'new')
        {
            $url = "mail/new/{$data['receiver']->guid}.html";
        }
        else
        {
            $url = "mail/reply/{$data['original_mail']->guid}.html";
        }
        $tmp = Array
        (
            Array
            (
                MIDCOM_NAV_URL => $url,
                MIDCOM_NAV_NAME => "{$data['receiver']->username} ({$data['receiver']->name})",
            ),
        );
        $this->_component_data['active_leaf'] = NET_NEHMER_MAIL_LEAFID_NEW;
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        $_MIDCOM->set_pagetitle($data['heading']);

        // Editing...
        return true;
    }

    /**
     * Sends a mail, according to the information from the component context.
     */
    function _send_mail(&$data)
    {
        $result = $data['receiver_mailbox']->deliver_mail
        (
            $_MIDCOM->auth->user,
            $data['datamanager']->types['subject']->value,
            $data['datamanager']->types['body']->value
        );
        if ($this->isError($result))
        {
            $data['error'] = $result;
            return;
            // This will exit.
        }

        if ($data['original_mail'])
        {
            $data['original_mail']->isread = true;
            $data['original_mail']->isreplied = true;
            $data['original_mail']->update();
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
            $result_outbox = $outbox->deliver_mail
            (
                $data['receiver'],
                $data['datamanager']->types['subject']->value,
                $data['datamanager']->types['body']->value
            );
            if ($this->isError($result_outbox))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Failed to append E-Mail to outbox: {$result_outbox->message}");
                // This will exit.
            }
        }

        // Send notification Mail
        $this->_send_notification_mail($result, &$data);
    
        // Relocate to the selected target
        if ($data['relocate_to'] === null)
        {
            $dest = "mail/sent/{$result}.html";
        }
        else
        {
            $dest = $data['relocate_to'];
        }

        if ($data['return_to'])
        {
            $dest .= (strpos($dest, '?') === FALSE) ? '?' : '&';
            $dest .= 'net_nehmer_mail_return_to=';
            $dest .= urlencode($data['return_to']);
        }

        $_MIDCOM->relocate($dest);
        // This will exit.
    }

    /**
     * Shows the send form. Nothing special.
     */
    function _show_mail_new($handler_id, &$data)
    {
        midcom_show_style('mail-show-new');
    }

    /**
     * Prepares the mail for showing. This is used only if no outbox is available.
     *
     * If set, the $_REQUEST key net_nehmer_mail_return_to is added into the
     * request context as return_to (if not, the key is set to null).
     */
    function _handler_mail_sent($handler_id, $args, &$data)
    {
        $data['mail'] = new net_nehmer_mail_mail($args[0]);
        if (! $data['mail'])
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = 'E-Mail ID unknown.';
            return false;
        }
        $mailbox = $data['mail']->get_mailbox();
        $data['receiver'] =& $_MIDCOM->auth->get_user($mailbox->owner);

        if (array_key_exists('net_nehmer_mail_return_to', $_REQUEST))
        {
            $data['return_to'] = $_REQUEST['net_nehmer_mail_return_to'];
        }
        else
        {
            $data['return_to'] = null;
        }

        $this->_component_data['active_leaf'] = $mailbox->guid;
        $_MIDCOM->set_26_request_metadata(time(), $this->_request_data['mail']->guid);
        $tmp = Array
        (
            Array
            (
                MIDCOM_NAV_URL => "mail/sent/{$data['mail']->guid}.html",
                MIDCOM_NAV_NAME => $data['mail']->subject,
            ),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        $_MIDCOM->set_pagetitle($data['mail']->subject);

        $tmp = Array
        (
            Array
            (
                MIDCOM_NAV_URL => "mail/new/{$data['receiver']->guid}.html",
                MIDCOM_NAV_NAME => $this->_l10n->get('mail sent successfully'),
            ),
        );
        $this->_component_data['active_leaf'] = NET_NEHMER_MAIL_LEAFID_NEW;

        return true;
    }

    /**
     * Shows a sent mail. This is used only if no outbox is available.
     */
    function _show_mail_sent($handler_id, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $data['newmail_url'] = "{$prefix}mail/new/{$data['receiver']->guid}.html";
        $data['name_translated'] = $this->_l10n->get('outbox');
        $data['body_formatted'] = $data['mail']->get_body_formatted();
        $data['return_url'] = "{$prefix}mailbox/OUTBOX.html";

        midcom_show_style('mail-show-sent');
    }

    /**
     * Sends a mail to the user notifying him about a new mail in his inbox. This
     * is called by _send_mail(). It will use sudo privileges to obtain the
     * neccessary access rights to the receiving user's person account (it might
     * not be readable without it). If no email address is set or the method is
     * unable to obtain sudo privileges, no mail is sent.
     *
     * The mail content is configured using the configuration keys
     * notification_mail_sender, notification_mail_subject and
     * notification_mail_body. The values are passed through the L10n system before
     * they are processed using midcom_helper_mailtemplate.
     *
     * Available template keys:
     *
     * - SENDER maps to the midcom_core_user object of user sending the mail.
     * - RECEVIER accordingly maps to the midcom_core_user of the receiver.
     * - MAILURL contains the URL to display the mail.
     * - SUBJECT contains the subject of the message.
     *
     * @param string $mail_guid The GUID of the mail which has been sent.
     * @param Array $data The request data.
     * @access private
     */
    function _send_notification_mail($mail_guid, &$data)
    {
        if (! $this->_config->get('enable_email_notify'))
        {
            return;
        }

        if ($_MIDCOM->auth->request_sudo())
        {
            $person = $data['receiver']->get_storage();
            $_MIDCOM->auth->drop_sudo();
        }
        else
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to obtain sudo privileges to load person record for mailing. Skipping mail send.', MIDCOM_LOG_INFO);
            debug_pop();
            return;
        }
        if (! $person->email)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('No E-Mail address configured. Skipping mail send.', MIDCOM_LOG_INFO);
            debug_pop();
            return;
        }

        $from = $this->_config->get('notification_mail_sender');
        if (! $from)
        {
            $from = $person->email;
        }
        $template = Array(
            'from' => $from,
            'reply-to' => '',
            'cc' => '',
            'bcc' => '',
            'x-mailer' => '',
            'subject' => $this->_l10n->get($this->_config->get('notification_mail_subject')),
            'body' => $this->_l10n->get($this->_config->get('notification_mail_body')),
            'body_mime_type' => 'text/plain',
            'charset' => 'UTF-8',
        );

        $url = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
            . "mail/show/{$mail_guid}.html";

        $mail = new midcom_helper_mailtemplate($template);
        $parameters = Array
        (
            'SENDER' => & $_MIDCOM->auth->user,
            'RECEIVER' => & $data['receiver'],
            'USERNAME' => $data['receiver']->username,
            'MAILURL' => $url,
            'SUBJECT' => $data['datamanager']->types['subject']->value,
        );
        $mail->set_parameters($parameters);
        $mail->parse();
        $mail->send($person->email);
    }



}

?>
