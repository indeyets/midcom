<?php
/**
 * @package net.nehmer.mail
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is an Mail view handler class for net.nehmer.mail
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 * 
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
     * Simple default constructor.
     */
    function net_nehmer_mail_handler_mail_admin()
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
    }

    function _handler_delete($handler_id, $args, &$data)
    {
        if (   $handler_id == 'mail-manage-delete-one'
            && !isset($args[0]))
        {
            debug_push_class(__CLASS__, __FUNCTION__);        
            debug_add("Didn't get mail guid as parameter.");
            debug_pop();            
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Invalid parameters given! See debug log for details.");
            // This will exit
        }
        
        $return_url = "";        
        $relocate = false;
        
        if (isset($_REQUEST['return_url']))
        {
            $return_url = $_REQUEST['return_url'];
        }
        
        switch($handler_id)
        {
            case 'mail-manage-delete-one':
                $this->_mail = new net_nehmer_mail_mail($args[0]);
                $this->_mailbox =& $this->_mail->get_mailbox();
                
                if (! $this->_mail)
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Couldn't find mail: {$args[0]}.");
                    // This will exit.
                }
                if (array_key_exists('net_nehmer_mail_action_delete', $_REQUEST))
                {
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
                        $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('mail deleted successfully'), 'ok');
                    }

                    $relocate = true;
                }
                break;
            case 'mail-manage-delete':
                $messages = array();
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
                
                if (array_key_exists('net_nehmer_mail_action_delete', $_REQUEST))
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
                }
                
                $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('successfully deleted selected mails'), 'ok');
                
                $relocate = true;
                break;
        }        
        
        if ($relocate)
        {
            $_MIDCOM->relocate($return_url);
        }

        $this->_prepare_request_data($handler_id);
        $_MIDCOM->set_pagetitle(sprintf($this->_l10n->get('really delete mail %s?'), $this->_mail->subject));
        
        return true;
    }
    
    function _show_delete($handler_id, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        $data['return_url'] = $prefix . $this->_mailbox->get_view_url();
        
        $data['delete_url'] = "{$prefix}mail/manage/delete/{$this->_mail->guid}.html";
        $data['delete_button_name'] = 'net_nehmer_mail_action_delete';
        
        midcom_show_style('mail-manage-delete');
    }
}

?>