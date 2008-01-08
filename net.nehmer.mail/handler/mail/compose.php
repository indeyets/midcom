<?php
/**
 * @package net.nehmer.mail
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a Mailbox admin handler class for net.nehmer.mail
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 *
 * @see midcom_baseclasses_components_handler
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
    var $_use_live_preview = false;

    var $_using_chooser = false;

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

        $this->_request_data['use_live_preview'] = $this->_use_live_preview;
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

        if ($this->_schemadb['new_mail']->fields['receivers']['widget'] == 'chooser')
        {
            $this->_using_chooser = true;
        }
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

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
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

        if ($this->_config->get('enable_live_preview'))
        {
            $this->_use_live_preview = true;
        }

        $url = null;

        if ($handler_id == 'mail-compose-new-quick')
        {
            $user = $_MIDCOM->auth->get_user($args[0]);
            $data['receiver'] =& $user->get_storage();

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
                break;
            case 'mail-compose-new':
                $receivers_key = 'receivers';
                if ($this->_using_chooser)
                {
                    $receivers_key = 'net_nehmer_mail_receivers_chooser_widget_selections';
                }

                if (   !isset($_POST[$receivers_key])
                    || empty($_POST[$receivers_key]))
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Couldn't find any receivers in data.");
                }
                debug_print_r("_POST[{$receivers_key}]: ",$_POST[$receivers_key]);
                foreach ($_POST[$receivers_key] as $receiver_id => $selected)
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
        if ($data['use_live_preview'])
        {
            $data['showdown_lib'] = MIDCOM_STATIC_URL . "/net.nehmer.mail/js/showdown.pack.js";
        }

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
            net_nehmer_mail_viewer::send_notification(&$receiver, &$this->_mail, &$this->_config);
        }
    }

}

?>