<?php
/**
 * @package net_nemein_notifications
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Class for sending notices. All component-specific notification senders should inherit from here.
 *
 * @package net_nemein_notifications
 */
class net_nemein_notifications_notifier
{    
    public $recipient = null;
    public $configuration = null;
    
    public function __construct($recipient)
    {
        $this->load_config();
        
        $this->recipient = new midgard_person($recipient);
    }
    
    private function load_config()
    {
        $this->configuration = new midcom_core_services_configuration_yaml('net_nemein_notifications');
    }
    
    /**
     * Stores the notification into database for later viewing
     */
    public function save_notification($message)
    {
        $notification = new net_nemein_notifications_notification();
        $notification->recipient = $this->recipient->id;

        // if ($_MIDCOM->auth->user)
        // {
        //     $user = $_MIDCOM->auth->user->get_storage();
        //     $notification->sender = $user->id;
        // }
        
        if (array_key_exists('action', $message))
        {
            $action_parts = explode(':', $message['action']);
            $notification->component = $action[0];
            $notification->action = $action[1];            
        }

        if (array_key_exists('title', $message))
        {
            $notification->title = $message['title'];
        }

        if (array_key_exists('abstract', $message))
        {
            $notification->abstract = $message['abstract'];
        }

        if (array_key_exists('content', $message))
        {
            $notification->content = $message['content'];
        }

        // TODO: Handle files

        return $notification->create();
    }
    
    public function send_mail($message, $force_save=false)
    {
        if (   !$this->recipient
            || empty($this->recipient->email))
        {
            return false;
        }
        
        $mail = new net_nemein_notifications_api_mail();
        
        $mail->to = $this->recipient->email;
        
        $sender = null;
        $from = null;
        if (   array_key_exists('from', $message)
            && !empty($message['from']))
        {            
            if (   mgd_is_guid($message['from'])
                || is_int($message['from']))
            {
                $sender = new midgard_person($message['from']);                
            }
            else
            {
                $from = $message['from'];
            }
        }
        else if ($_MIDGARD['user'])
        {
            //TODO: Get the user
        }
        
        if (   !is_null($sender)
            && is_null($from)
            && $sender->email)
        {
            $mail->from = '"' . $sender->firstname . " " . $sender->lastname . '" <' . $sender->email . '>';
        }
        else if (! is_null($from))
        {
            $mail->from = $from;
        }
        else
        {
            $mail->from = $this->configuration->get('fallback_mail_from');
        }
        
        
        $mail->send();
        
        if (   $this->configuration->get('auto_save')
            || $force_save)
        {
            $this->save_notification($message);
        }
    }
}

?>