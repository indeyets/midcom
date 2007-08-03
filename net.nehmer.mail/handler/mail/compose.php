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
    var $original_mail = null;
    
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
    }
    
    /**
     * Loads and prepares the schema database.
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb =& midcom_helper_datamanager2_schema::load_database( $this->_config->get('schemadb') );
        
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
        $this->_load_schemadb();
        
        $this->_controller = midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schemaname = 'new_mail';
        $this->_controller->defaults = $this->_defaults;
        $this->_controller->callback_object =& $this;

        if ($this->_return_to !== null)
        {
            $this->_controller->formmanager->form->addElement('hidden', 'net_nehmer_mail_return_to', $this->_return_to);
        }
        if ($this->_relocate_to !== null)
        {
            $this->_controller->formmanager->form->addElement('hidden', 'net_nehmer_mail_relocate_to', $this->_relocate_to);
        }

        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for mail.");
            // This will exit.
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
            $data['original_mail'] = new net_nehmer_mail_mail($args[0]);
            if (! $data['original_mail'])
            {
                $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Original mail {$args[0]} couldn't be found.");
            }
            $receiver = $_MIDCOM->auth->get_user($data['original_mail']->sender);
            $data['heading'] = sprintf($this->_l10n->get('write reply to %s:'), $receiver->name);

            $url = "mail/compose/reply/{$data['original_mail']->guid}.html";
        }
        else if ($handler_id == 'mail-compose-reply-all')
        {
            $data['original_mail'] = new net_nehmer_mail_mail($args[0]);
            if (! $data['original_mail'])
            {
                $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Original mail {$args[0]} couldn't be found.");
            }
            
            $data['heading'] = $this->_l10n->get('write reply to all');
            
            $url = "mail/compose/replyall/{$data['original_mail']->guid}.html";
        }

        $this->_load_controller($handler_id);
        $this->_prepare_request_data($handler_id);

        switch ($this->_controller->process_form())
        {
            case 'send':
                // Relocate to the selected target
                if ($data['relocate_to'] === null)
                {
                    $dest = "mail/compose/sent/{$this->_mail->guid}";
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
                        $receivers[] =& $_MIDCOM->auth->get_user($receiver_id);
                    }
                }
                break;
            case 'mail-compose-reply':
                $receivers =& $_MIDCOM->auth->get_user($_request_data['original_mail']->sender);
                break;
            case 'mail-compose-replyall':
                $receivers =& $_request_data['original_mail']->get_receivers();
                break;
        }
        
        if (empty($receivers))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Couldn't find any receivers in data.");
        }
        
        $this->_mail = new net_nehmer_mail_mail();
        $this->_mail->sender = $_MIDCOM->auth->user->guid;
        $this->_mail->subject = $_POST['subject'];
        $this->_mail->body = $_POST['body'];
        $this->_mail->received = time();
        $this->_mail->isread = false;        

        if (! $this->_mail->create())
        {
            // This should normally not fail, as the class default privilege is set accordingly.
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('Mail object was:', $this->_mail);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a mail record. See the debug level log for details.');
            // This will exit.
        }

        $this->_mail->deliver_to(&$receivers);
                
        debug_pop();

        return $this->_mail;
    }
    
    function _show_create($handler_id, &$data)
    {
        if ($handler_id == 'mail-compose-new')
        {
            midcom_show_style('mail-compose-new');
        }
        else
        {
            midcom_show_style('mail-compose-replay');            
        }
    }

}

?>