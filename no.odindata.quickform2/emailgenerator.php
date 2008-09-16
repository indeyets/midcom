<?php
/**
 * @package no.odindata.quickform2
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class you need to extend to change the formatting of the
 * quickform email.
 *
 * You should extend this class and change the execute method only.
 *
 * @package no.odindata.quickform2
 */
class no_odindata_quickform2_emailgenerator
{
    /**
     * @var object The org_openpsa_mail object to be sent to the contact.
     */
    var $mail;
    
    /**
     * @var object org_openpsa_mail to send to the form submitter.
     */
    var $recipient;
    
    /**
     * The submitted values
     * @var array
     */
     
    var $values;
    /**
     * @var object midcom_helper_datamanager2_schema
     */
    var $schema;

    /**
     * The from adress set in the config as sender address
     * @var string
     */
    var $from;
    
    /**
     * @var string charset
     */
    var $encoding;
    
    /**
     *  the receipt message
     * @var string
     */
    var $recipient_msg = "";
    
    /**
     * @var boolean
     */
    var $add_recipient_data;

    function __construct($mail, $recipient)
    {
        $this->mail = $mail;
        $this->recipient = $recipient;
        $this->mail->headers = array();
        $this->mail->body = '';
        $this->recipient->headers = array();
    }

    function execute()
    {
       $this->_create_recipient_body() ;
       $this->_create_mail_body();
    }
    /**
     * You shouldn't need to extend this method
     * It encapsulates the sending
     */
    function send()
    {
        if ($this->mail_recipient && $this->recipient->to)
        {
            debug_push(__CLASS__, __FUNCTION__);
            if (!$this->recipient->send())
            {
                debug_add(sprintf("Mail to recipient failed: mail(%s, %s, %s)", $this->recipient->to, $this->recipient->subject,
                            $this->recipient->headers), MIDCOM_LOG_INFO);
                debug_pop();
                return false;
            }
            debug_add(sprintf ("Mail to recipient %s sent SUCCESSFULLY", $this->recipient->to), MIDCOM_LOG_INFO);
            debug_pop();
        }

        if (!$this->mail->send())
        {
            debug_push(__CLASS__, __FUNCTION__);
            debug_add('Could not send email', MIDCOM_LOG_INFO);
            //debug_print_r('Email:', $this->mail);
            debug_pop();
        }
        
        return true;

    }
    /**
     * Creates the recipient email body.
     * extend this function if you want to have something else in the email.
     */
    function _create_recipient_body()
    {
        $this->recipient->body = $this->recipient_msg;
        $this->recipient->body .= $this->_create_body();
    }

    /**
     * Creates the email to the administrator /contact handler
     * extend this function if you want to have something else in the email.
     * @return  string the email body.
     */
    function _create_mail_body()
    {
        $this->mail->body = $this->_create_body();
        $this->mail->body .= "\n-- \nMail submitted on " . strftime('%x %X', time());
        $this->mail->body .= "\nFrom IP: {$_SERVER['REMOTE_ADDR']}";
    }

    /**
     * Creates the basic email body.
     * @return string a simple formated string
     */
    function _create_body()
    {
        $message = '';

        foreach ($this->values as $key => $field)
        {
            $name = $this->schema->fields[$key]['title'];
            if (is_a($field, 'midcom_helper_datamanager2_type_select'))
            {
                $index = $field->convert_to_storage(null);
                $value = $field->get_name_for_key($index);

            }
            else
            {
                $value = $field->convert_to_storage(null);
            }
            
            $message .= sprintf("%s: %s\n", $name , $value);

        }
        
        return $message;
    }

    /**
     * Add the receipt data
     * @param boolean $add
     */
    function set_add_recipient_data ($add)
    {
        $this->add_recipient_data = $add;
    }
    /**
     * Set email encoding
     */
    function set_charset($enc)
    {
        debug_pop(__FUNCTION__, __CLASS__);
        $this->encoding = $enc;
        $this->mail->encoding = $enc;
        $ths->recipient->encoding = $enc;

        //$this->mail->headers["Content-Type"] = "text/plain; charset={$enc}";
        //$this->recipient->headers["Content-Type"] = "text/plain; charset={$enc}";
        debug_add("Settingencoding : $enc");
        debug_pop();
    }
    /**
     * Set to true if the submitter should get a receipt of the
     * email
     * @param boolean $send true if mail should be sent
     */
    function set_send_recipient($send)
    {
        $this->mail_recipient = $send;
    }

    /**
     * Sets the receipt message
     * @param string $message
     */
    function set_recipient_message($message)
    {
       $this->recipient_msg = $message . "\n";
    }
    /**
     * The fromaddress
     * @param string $from
     */
    function set_from($from)
    {
        $this->from = $from;
        $this->mail->headers['from'] = $from;
        $this->mail->headers['return-path'] = $from;
        $this->recipient->headers['from'] = $from;
        $this->recipient->headers['return-path'] = $from;


    }
    /**
     * Sets the reply_to address
     * @param $to string
     */
    function set_reply_to ($to)
    {
        if ($to)
        {
            $mail->headers['reply-to'] = $to;
        }
        // the old qf set reply-to to from. I do not to that here as you cannot know if from isset.

    }
    /**
     * @param array $values array the values submitted in the form
     */
    function set_values($values)
    {
        $this->values = $values;

        if (array_key_exists('email', $this->values))
        {
            $this->mail->from = $this->values['email']->value;
            $this->recipient->to = $this->values['email']->value;
        }
        else
        {
            $this->mail->from = $this->from->value;
        }

    }
    /**
     * @param the form schema
     */
    function set_schema ($schema)
    {
        $this->schema = $schema;
    }
    /**
     * @param string $subject the email subject string
     */
    function set_subject($subject , $subject_recipient)
    {
        $this->mail->headers['Subject']= $subject;
        if ($subject_recipient == null)
        {
            $subject_recipient = $subject;
        }
        $this->recipient->headers['Subject'] = $subject_recipient;
    }
    /**
     * This function sets the emailaddress that the submitted form will
     * be sent to.
     * @param string $to the mailaddress set in the configuration
     */
    function set_to ($to)
    {
        $this->mail->to = $to;
        $this->mail->headers['to'] = $to;
    }
}
?>