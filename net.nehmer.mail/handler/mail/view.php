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

class net_nehmer_mail_handler_mail_view extends midcom_baseclasses_components_handler
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
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * Current mailbox
     *
     * @var Array
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

    var $_replyall_enabled = false;

    var $_output_mode = null;

    /**
     * Simple default constructor.
     */
    function net_nehmer_mail_handler_mail_view()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_pop();
        parent::__construct();
    }

    /**
     * _on_initialize is called by midcom on creation of the handler.
     */
    function _on_initialize()
    {
        $_MIDCOM->auth->require_valid_user();

        //$this->_mailboxes =& net_nehmer_mail_mailbox::list_mailboxes();
    }

    function _populate_node_toolbar($handler_id)
    {
        // $this->_view_toolbar->add_item(Array(
        //     MIDCOM_TOOLBAR_URL => "admin/create.html",
        //     MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('create'),
        //     MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
        //     MIDCOM_TOOLBAR_ACCESSKEY => 'c',
        //     MIDCOM_TOOLBAR_HIDDEN => ! $_MIDCOM->auth->can_user_do('midgard:create', null, 'net_nehmer_mail_mailbox'),
        // ));
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data($handler_id)
    {
        $this->_request_data['controller'] =& $this->_controller;

        $this->_request_data['replyall_enabled'] = $this->_replyall_enabled;

        $this->_request_data['mailbox'] =& $this->_mailbox;
        $this->_request_data['mail'] =& $this->_mail;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_view($handler_id, $args, &$data)
    {
        $this->_mail = new net_nehmer_mail_mail($args[0]);

        if (! $this->_mail)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Couldn't find mail: {$args[0]}.");
            // This will exit.
        }

        $this->_mailbox =& $this->_mail->get_mailbox();

        if (! $this->_mailbox)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Couldn't find mails mailbox: {$args[0]}.");
            // This will exit.
        }

        $receivers =& $this->_mail->get_receivers();

        if (count($receivers) > 1)
        {
            $this->_replyall_enabled = true;
        }

        $this->_request_data['is_sent'] = false;
        if (strtolower($this->_mailbox->name) == 'outbox')
        {
            $this->_request_data['is_sent'] = true;
        }

        if ($this->_mail->status == NET_NEHMER_MAIL_STATUS_UNREAD)
        {
            $this->_mail->set_status(NET_NEHMER_MAIL_STATUS_READ);
        }

        $this->_prepare_request_data($handler_id);
        $_MIDCOM->set_pagetitle($this->_l10n->get($this->_mailbox->name) . ' :: ' . $this->_mail->subject);

        $tmp = Array
        (
            Array
            (
                MIDCOM_NAV_URL => $this->_mailbox->get_view_url() . ".html",
                MIDCOM_NAV_NAME => $this->_l10n->get($this->_mailbox->name),
            ),
            Array
            (
                MIDCOM_NAV_URL => "view/mail/{$this->_mail->guid}.html",
                MIDCOM_NAV_NAME => $this->_mail->subject,
            ),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     */
    function _handler_live_preview($handler_id, $args, &$data)
    {
        $schemadb =& midcom_helper_datamanager2_schema::load_database( $this->_config->get('schemadb') );
        if (array_key_exists('type_config',$schemadb['new_mail']->fields['body']))
        {
            if (array_key_exists('output_mode',$schemadb['new_mail']->fields['body']['type_config']))
            {
                $this->_output_mode = $schemadb['new_mail']->fields['body']['type_config']['output_mode'];
            }
        }

        $this->_request_data['live_preview_content'] = '';
        if (array_key_exists('live_preview_content',$_REQUEST))
        {
            $this->_request_data['live_preview_content'] = $_REQUEST['live_preview_content'];
        }
    }

    /**
     * Mail content view.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     * @access private
     */
    function _show_view($handler_id, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        $user = $_MIDCOM->auth->get_user($this->_mail->sender);
        $data['sender'] =& $user->get_storage();

        $data['return_url'] = $prefix . $this->_mailbox->get_view_url();
        $data['new_url'] = "{$prefix}mail/compose/new/{$this->_mail->sender}.html";
        $data['reply_url'] = "{$prefix}mail/compose/reply/{$this->_mail->guid}.html";
        if ($data['replyall_enabled'])
        {
            $data['replyall_url'] = "{$prefix}mail/compose/replyall/{$this->_mail->guid}.html";
        }
        $data['body_formatted'] = $this->_mail->get_body_formatted();

        $data['can_delete'] = $_MIDCOM->auth->can_do('midgard:delete', $this->_mail);
        $data['delete_url'] = "{$prefix}mail/admin/delete/{$this->_mail->guid}.html";

        midcom_show_style('mail-show');
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_live_preview($handler_id, &$data)
    {
        if ($this->_output_mode == 'markdown')
        {
            $markdown = new net_nehmer_markdown_markdown();
            echo $markdown->render($data['live_preview_content']);
        }
        else
        {
            echo $data['live_preview_content'];
        }
    }

}

?>