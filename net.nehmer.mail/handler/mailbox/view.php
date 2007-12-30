<?php
/**
 * @package net.nehmer.mail
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a Mailbox view handler class for net.nehmer.mail
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 *
 * @package net.nehmer.mail
 */

class net_nehmer_mail_handler_mailbox_view extends midcom_baseclasses_components_handler
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
     * List of users mailboxes
     *
     * @var Array
     * @access private
     */
    var $_mailboxes = array();

    /**
     * Current mailbox
     *
     * @var Array
     * @access private
     */
    var $_mailbox = null;

    var $_show_actions = true;

    /**
     * Simple default constructor.
     */
    function net_nehmer_mail_handler_mailbox_view()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_pop();
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * _on_initialize is called by midcom on creation of the handler.
     */
    function _on_initialize()
    {
        $_MIDCOM->auth->require_valid_user();

        $this->_mailboxes =& net_nehmer_mail_mailbox::list_mailboxes();
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

        $this->_request_data['mailboxes'] =& $this->_mailboxes;
        $this->_request_data['mailbox'] =& $this->_mailbox;

        $this->_request_data['show_actions'] = $this->_show_actions;
    }

    /**
     * Loads and prepares the schema database.
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb =& midcom_helper_datamanager2_schema::load_database( $this->_config->get('schemadb') );
    }

    /**
     * Internal helper, loads the controller for the current event. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller($handler_id)
    {
        $this->_load_schemadb();

        $this->_controller =& new midcom_helper_datamanager2_datamanager($this->_schemadb);

        if (   !$this->_controller
            || !$this->_controller->set_storage($this->_mailbox, 'mailbox'))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for mailbox {$this->_mailbox->id}.");
            // This will exit.
        }
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
	 */
    function _handler_view($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        debug_print_r('_mailboxes',$this->_mailboxes);

        if (   count($args) == 0
            || strtolower($args[0]) == 'inbox')
        {
            $this->_mailbox = net_nehmer_mail_mailbox::get_inbox();
        }
        else if (strtolower($args[0]) == 'outbox')
        {
            $this->_mailbox = net_nehmer_mail_mailbox::get_outbox();
            $this->_show_actions = false;
        }
        else
        {
            $this->_mailbox = new net_nehmer_mail_mailbox($args[0]);
        }

        if (! $this->_mailbox)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Couldn't find mailbox: {$args[0]}.");
            // This will exit.
        }

        $_MIDCOM->auth->require_do('net.nehmer.mail:list_mails', $this->_mailbox);

        //$data['mailbox_name'] = $this->_l10n->get($this->_mailbox->name);

        $mailbox_classname = 'other';
        if (   strtolower($this->_mailbox->name) == 'inbox'
            || strtolower($this->_mailbox->name) == 'outbox')
        {
            $mailbox_classname = $this->_mailbox->name;
        }
        $mailbox_classname = strtolower($mailbox_classname);
        $this->_request_data['mailbox_classname'] = $mailbox_classname;

        $this->_prepare_request_data($handler_id);
        $this->_populate_node_toolbar($handler_id);
        $_MIDCOM->set_pagetitle($this->_l10n->get($this->_mailbox->name));

        // $deleted_mails = $this->_mailbox->list_deleted_mails();
        // debug_print_r('$deleted_mails',$deleted_mails);

        debug_pop();
        return true;
    }

    /**
     * Mailbox content view.
     *
     * @access private
     */
    function _show_view($handler_id, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        $data['return_url'] = $prefix . $this->_mailbox->get_view_url();
        $data['action_handler_url'] = "{$prefix}mail/admin/perform.html";
        $data['perform_button_name'] = 'net_nehmer_mail_action_perform';

        $data['actions'] = array(
            'net_nehmer_mail_action_delete' => $this->_l10n_midcom->get('delete'),
            'net_nehmer_mail_action_mark_as_starred' => $this->_l10n->get('mark as starred'),
            'net_nehmer_mail_action_mark_as_unread' => $this->_l10n->get('mark as unread'),
        );

        if ($this->_config->get('paging') !== false)
        {
            $qb = $this->_mailbox->list_mails('reverse received', $this->_config->get('paging'));
            $data['qb_pager'] = $qb;
            $mails = $qb->execute();
        }
        else
        {
            $mails = $this->_mailbox->list_mails();
        }

        midcom_show_style('mailbox-items-start');

        if (count($mails) == 0)
        {
            midcom_show_style('mailbox-items-empty');
        }
        else
        {
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            foreach ($mails as $i => $mail)
            {
                $data['mail'] =& $mail;
                $data['row_class'] = 'even';
                if ($i % 2 == 0)
                {
                    $data['row_class'] = 'odd';
                }
                $data['sender'] =& $_MIDCOM->auth->get_user($mail->sender);
                $data['view_url'] = "{$prefix}mail/view/{$mail->guid}.html";
                $data['new_url'] = "{$prefix}compose/new/{$mail->sender}.html";
                $data['reply_url'] = "{$prefix}compose/reply/{$mail->guid}.html";

                midcom_show_style('mailbox-items-item');
            }
        }

        midcom_show_style('mailbox-items-end');
    }

}

?>