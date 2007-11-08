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
    
        // $this->_request_switch[] = Array
        // (
        //     'handler' => 'mail_show',
        //     'fixed_args' => Array('mail', 'show'),
        //     'variable_args' => 1,
        // );
        // 
        // $this->_request_switch[] = Array
        // (
        //     'handler' => 'mail_searchto',
        //     'fixed_args' => Array('mail', 'new'),
        // );
        // $this->_request_switch['new'] = Array
        // (
        //     'handler' => 'mail_new',
        //     'fixed_args' => Array('mail', 'new'),
        //     'variable_args' => 1,
        // );
        // $this->_request_switch['reply'] = Array
        // (
        //     'handler' => 'mail_new',
        //     'fixed_args' => Array('mail', 'reply'),
        //     'variable_args' => 1,
        // );
        // 
        // $this->_request_switch[] = Array
        // (
        //     'handler' => 'mail_sent',
        //     'fixed_args' => Array('mail', 'sent'),
        //     'variable_args' => 1,
        // );
        // 
        // $this->_request_switch[] = Array
        // (
        //     'handler' => 'mail_manage',
        //     'fixed_args' => Array('mail', 'manage'),
        // );
        
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
                MIDCOM_TOOLBAR_URL => 'config/',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            ));
        }
    }
    
    function get_unread_count()
    {
        $user = $_MIDCOM->auth->user->get_storage();
        $inbox = net_nehmer_mail_mailbox::get_inbox($user);
        $unread = $inbox->get_unseen_count();
        
        return $unread;
    }
    

    /**
     * Sends a notification to the user about a new mail in his inbox.
     *
     * The mail content is configured using the configuration keys
     * notification_mail_sender, notification_mail_subject and
     * notification_mail_body. The values are passed through the L10n system before
     * they are processed using midcom_helper_mailtemplate.
     *
     * Available template keys:
     *
     * - __SENDER_NAME__ name of user sending the mail.
     * - __RECEIVER_NAME__ name of the receiver.
     * - __SUBJECT__ contains the subject of the message.
     * - __MAILURL__ contains the URL to display the mail.
     *
     * @param midcom_person $person
     * @param net_nehmer_mail_mail $mail
     * @param config $config
     * @access private
     */
    function send_notification(&$person, &$mail, &$config)
    {   
        $recipient_guid = $person->guid;
        
        $mail_sender = $_MIDCOM->auth->get_user($mail->sender);
        $mail_sender =& $mail_sender->get_storage();

        $from = $config->get('notification_mail_sender');
        if (! $from)
        {
            $from = $mail_sender->guid;
        }
        
        $mail_url = $_MIDCOM->get_host_prefix() . $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "mail/view/{$mail->guid}/";
        
        $title = $_MIDCOM->i18n->get_string($config->get('notification_mail_subject'), 'net.nehmer.mail');
        $title = str_replace("__SENDER_NAME__", $mail_sender->name, $title);
        
        $abstract = $_MIDCOM->i18n->get_string($this->_config->get('notification_mail_abstract_body'), 'net.nehmer.mail');
        $abstract = str_replace("__SENDER_NAME__", $mail_sender->name, $abstract);
        
        $content = $_MIDCOM->i18n->get_string($this->_config->get('notification_mail_body'), 'net.nehmer.mail');
        $content = str_replace("__SENDER_NAME__", $mail_sender->name, $content);
        $content = str_replace("__RECEIVER_NAME__", $person->name, $content);
        $content = str_replace("__SUBJECT__", $mail->subject, $content);
        $content = str_replace("__MAILURL__", $mail_url, $content);
                        
        $message['title'] = $title;
        $message['from'] = $from;
        $message['abstract'] = $abstract;
        $message['content'] = $content;
        $message['growl_to'] = $person->name;
                
        org_openpsa_notifications::notify('net.nehmer.mail:new_mail', $recipient_guid, $message);
    }


}

?>
