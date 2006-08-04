<?php
/**
 * @package org.openpsa.notifications
 * @author Henri Bergius, http://bergie.iki.fi
 * @version $Id: notifier.php,v 1.2 2006/06/13 10:50:52 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
 
/** 
 * Class for sending notices. All component-specific notification senders should inherit from here.
 *
 * @package org.openpsa.notifications 
 */
class org_openpsa_notifications_notifier extends midcom_baseclasses_components_purecode
{    

    var $recipient = null;
    
    function org_openpsa_notifications_notifier($recipient)
    {
        $this->_component = 'org.openpsa.notifications';
        
        $this->recipient = new midcom_db_person($recipient);
    
        parent::midcom_baseclasses_components_purecode();
    }

    function send_email($message)
    {
        if (   !$this->recipient
            || empty($this->recipient->email))
        {
            return false;
        }
        
        $mail = new org_openpsa_mail();
        $mail->to = $this->recipient->email;
        
        $sender = $_MIDCOM->auth->user->get_storage();
        if (   $sender
            && $sender->email)
        {
            $mail->from = '"' . $sender->name . '" <' . $sender->email . '>';
        }
        else
        {
            $mail->from = '"OpenPsa Notifier" <noreply@openpsa.org>';
        }

        if (array_key_exists('subject', $message))
        {
            $mail->subject = $message['subject'];
        }
        
        if (array_key_exists('body', $message))
        {
            $mail->body = $message['body'];
        }
        // TODO: In other case print all keys and values of the array in message body, n.o.quickform-like
        
        $ret = $mail->send();
        if (!$ret)
        {
            debug_add("failed to send notification email to {$mail->to}, reason: " . $mail->get_error_message(), MIDCOM_LOG_WARN);
        }
        else
        {
            $_MIDCOM->uimessages->add("notification sent to {$mail->to}");
        }
        return $ret;
    }
}
?>