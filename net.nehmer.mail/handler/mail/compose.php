<?php
/**
 * @package net.nehmer.mail
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is an Mailbox admin handler class for net.nehmer.mail
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 * 
 * @package net.nehmer.mail
 */

class net_nehmer_mail_handler_mail_compose extends midcom_baseclasses_components_handler 
{
    /**
     * The Controller
     *
     * @var mixed
     * @access private
     */
    var $_controller = null;

    /**
     * The schema database in use, available only while a datamanager is loaded.
     *
     * @var mixed
     * @access private
     */
    var $_schemadb = null;

    /**
     * The defaults to use for the new mail.
     *
     * @var Array
     * @access private
     */
    var $_defaults = array();

    /**
     * Current mail object
     *
     * @var net_nehmer_mail_mail
     * @access private
     */
    var $_mail = null;
    
    /**
     * Original mail object in reply
     *
     * @var net_nehmer_mail_mail
     * @access private
     */
    var $_original_mail = null;
    
    var $_return_to = null;
    var $_relocate_to = null;
    var $_compose_type = null;
    
    /**
     * Simple default constructor.
     */
    function net_nehmer_mail_handler_mail_compose()
    {
        parent::midcom_baseclasses_components_handler();
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
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['defaults'] =& $this->_defaults;

        $this->_request_data['mail'] =& $this->_mail;
        $this->_request_data['original_mail'] =& $this->_original_mail;
                
        $this->_request_data['return_to'] =& $this->_return_to;
        $this->_request_data['relocate_to'] =& $this->_relocate_to;
        
        $this->_request_data['in_compose_view'] = true;

        $this->_request_data['compose_type'] =& $this->_compose_type;
        $this->_request_data['original_mail'] =& $this->_original_mail;
    }
    
    /**
     * Loads and prepares the schema database.
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb($handler_id)
    {
        $this->_schemadb =& midcom_helper_datamanager2_schema::load_database( $this->_config->get('schemadb') );
        
        if ($handler_id == 'mail-compose-new-quick')
        {
            $this->_schemadb['new_mail']->fields['receivers']['hidden'] = true;
        }
        
        if ($handler_id == 'mail-compose-reply')
        {
            $this->_schemadb['new_mail']->fields['receivers']['hidden'] = true;            
        }

        if (   $handler_id == 'mail-compose-reply'
            || $handler_id == 'mail-compose-reply-all')
        {
            //$this->_schemadb['new_mail']->fields['receivers']['hidden'] = true;
            $this->_defaults['subject'] = $this->_l10n->get('re:') . ' ' . $this->_original_mail->subject;
            $receivers = $this->_original_mail->get_receivers();
            $receiver_list = array();
            foreach ($receivers as $k => $receiver)
            {
                $receiver_list[] = $receiver->id;
            }
            $this->_defaults['receivers'] =& $receiver_list;
        }

        $session =& new midcom_service_session();
        if ($session->exists('failed_POST_data'))
        {
            $this->_defaults = $session->get('failed_POST_data');
            $session->remove('failed_POST_data');
        }
        unset($session);
    }

    /**
     * Internal helper, loads the controller for the current event. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller($handler_id)
    {
        $this->_load_schemadb($handler_id);
        
        $this->_controller = midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schemaname = 'new_mail';
        $this->_controller->defaults = $this->_defaults;
        $this->_controller->callback_object =& $this;
        
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for mail.");
            // This will exit.
        }

        if ($handler_id == 'mail-compose-reply-all')
        {
            $this->_controller->formmanager->widgets['receivers']->freeze();
        }

        if ($this->_return_to !== null)
        {
            $this->_controller->formmanager->form->addElement('hidden', 'net_nehmer_mail_return_to', $this->_return_to);
        }
        if ($this->_relocate_to !== null)
        {
            $this->_controller->formmanager->form->addElement('hidden', 'net_nehmer_mail_relocate_to', $this->_relocate_to);
        }
    }
    
    function _handler_create($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $_MIDCOM->auth->require_user_do('midgard:create', null, 'net_nehmer_mail_mail');
        
        $this->_compose_type = $handler_id;
        
        if (array_key_exists('net_nehmer_mail_return_to', $_REQUEST))
        {
            $this->_return_to = $_REQUEST['net_nehmer_mail_return_to'];
        }

        if (array_key_exists('net_nehmer_mail_relocate_to', $_REQUEST))
        {
            $this->_relocate_to = $_REQUEST['net_nehmer_mail_relocate_to'];
        }
        
        $url = null;
        
        if ($handler_id == 'mail-compose-new-quick')
        {
            $data['receiver'] =& $_MIDCOM->auth->get_user($args[0]);

            $data['heading'] = sprintf($this->_l10n->get('write mail to %s:'), $data['receiver']->name);
        }
        else if ($handler_id == 'mail-compose-new')
        {
            $data['heading'] = $this->_l10n->get('compose mail');

            $url = "mail/compose/new.html";
        }
        else if ($handler_id == 'mail-compose-reply')
        {
            $this->_original_mail = new net_nehmer_mail_mail($args[0]);
            if (! $this->_original_mail)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Original mail {$args[0]} couldn't be found.");
            }
            $receiver = $_MIDCOM->auth->get_user($this->_original_mail->sender);
            $data['heading'] = sprintf($this->_l10n->get('write reply to %s:'), $receiver->name);

            $url = "mail/compose/reply/{$this->_original_mail->guid}.html";
        }
        else if ($handler_id == 'mail-compose-reply-all')
        {
            $this->_original_mail = new net_nehmer_mail_mail($args[0]);
            if (! $this->_original_mail)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Original mail {$args[0]} couldn't be found.");
            }

            $receivers =& $this->_original_mail->get_receivers(false);
            if (count($receivers) < 2)
            {
                $_MIDCOM->relocate("mail/compose/reply/{$this->_original_mail->guid}.html");
            }
            
            $data['heading'] = $this->_l10n->get('write reply to all');
            
            $url = "mail/compose/replyall/{$this->_original_mail->guid}.html";
        }

        $this->_load_controller($handler_id);
        $this->_prepare_request_data($handler_id);

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Relocate to the selected target
                if ($this->_relocate_to === null)
                {
                    $dest = "mail/view/{$this->_mail->guid}";
                }
                else
                {
                    $dest = $this->_relocate_to;
                }

                if ($this->_return_to)
                {
                    $dest .= (strpos($dest, '?') === false) ? '?' : '&';
                    $dest .= 'net_nehmer_mail_return_to=';
                    $dest .= urlencode($this->_return_to);
                }
                
                debug_add("relocate: {$dest}");
                
                $_MIDCOM->relocate($dest);
                // This will exit.
            case 'cancel':
                $_MIDCOM->relocate("");
                // This will exit.
        }
        
        if (! is_null($url))
        {
            $tmp = Array
            (
                Array
                (
                    MIDCOM_NAV_URL => $url,
                    MIDCOM_NAV_NAME => $this->_l10n->get('compose mail'),
                ),
            );
            $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
            $_MIDCOM->set_pagetitle($data['heading']);
        }
        
        debug_pop();
        return true;
    }

    /**
     * DM2 creation callback, binds to the current content topic.
     */
    function &dm2_create_callback(&$controller)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $receivers = array();
        switch($this->_compose_type)
        {
            case 'mail-compose-new-quick':
                $receivers[] = $this->_request_data['receiver'];
            case 'mail-compose-new':
                if (   !is_array($_POST['receivers'])
                    || empty($_POST['receivers']))
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Couldn't find any receivers in data.");
                }
                debug_print_r('_POST[receivers]: ',$_POST['receivers']);
                foreach ($_POST['receivers'] as $receiver_id => $selected)
                {
                    if ($selected)
                    {
                        $user =& $_MIDCOM->auth->get_user($receiver_id);
                        $receivers[] =& $user->get_storage();
                    }
                }
                break;
            case 'mail-compose-reply':
                $user =& $_MIDCOM->auth->get_user($this->_original_mail->sender);
                $receivers[] =& $user->get_storage();
                break;
            case 'mail-compose-reply-all':
                $receivers =& $this->_original_mail->get_receivers(false);
                break;
        }

        if (empty($receivers))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Couldn't find any receivers in data.");
        }
        
        $current_user = $_MIDCOM->auth->user->get_storage();
        
        $this->_mail = new net_nehmer_mail_mail();
        $this->_mail->sender = $_MIDCOM->auth->user->guid;
        $this->_mail->subject = $_POST['subject'];
        $this->_mail->body = $_POST['body'];
        $this->_mail->received = time();
        $this->_mail->status = NET_NEHMER_MAIL_STATUS_SENT;
        $this->_mail->owner = $current_user->id;
                
        if (! $this->_mail->create())
        {
            // This should normally not fail, as the class default privilege is set accordingly.
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('Mail object was:', $this->_mail);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a mail record. See the debug level log for details. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        $this->_mail->deliver_to(&$receivers);
        $this->_send_notifications(&$receivers);
       
        debug_pop();

        return $this->_mail;
    }
    
    function _show_create($handler_id, &$data)
    {
        if ($handler_id == 'mail-compose-new')
        {
            $data['compose_type'] = 'new';
            midcom_show_style('mail-compose-new');
        }
        else
        {
            $data['compose_type'] = 'reply';
            midcom_show_style('mail-compose-reply');            
        }
    }
    
    function _send_notifications(&$receivers)
    {
        if (! $this->_config->get('enable_notifications'))
        {
            return;
        }
        
        foreach ($receivers as $receiver)
        {
            $this->_send_notification(&$receiver);
        }
    }
    
    /**
     * Sends a notification to the user about a new mail in his inbox.
     * This is called by _send_notifications().
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
     * @param string $mail_guid The GUID of the mail which has been sent.
     * @param Array $data The request data.
     * @access private
     */
    function _send_notification(&$person)
    {   
        $recipient_guid = $person->guid;
        
        $mail_sender = $_MIDCOM->auth->get_user($this->_mail->sender);
        $mail_sender =& $mail_sender->get_storage();

        $from = $this->_config->get('notification_mail_sender');
        if (! $from)
        {
            $from = $mail_sender->guid;
        }
        
        $mail_url = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "mail/view/{$this->_mail->guid}.html";
        
        $title = $this->_l10n->get($this->_config->get('notification_mail_subject'));
        $title = str_replace("__SENDER_NAME__", $mail_sender->name, $title);
        
        $abstract = $this->_l10n->get($this->_config->get('notification_mail_abstract_body'));
        $abstract = str_replace("__SENDER_NAME__", $mail_sender->name, $abstract);
        
        $content = $this->_l10n->get($this->_config->get('notification_mail_body'));
        $content = str_replace("__SENDER_NAME__", $mail_sender->name, $content);
        $content = str_replace("__RECEIVER_NAME__", $person->name, $content);
        $content = str_replace("__SUBJECT__", $this->_mail->subject, $content);
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