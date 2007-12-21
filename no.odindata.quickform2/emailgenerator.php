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
    var $reciept;
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
    var $reciept_msg = "";
    /**
     * @var boolean
     */
    var $add_reciept_data;



    function no_odindata_quickform2_emailgenerator ( $mail, $reciept  )
    {
        $this->mail = $mail;
        $this->reciept = $reciept;
        $this->mail->headers = array();
        $this->mail->body = '';
        $this->reciept->headers = array(  );
    }


    function execute(   )
    {

       $this->_create_recipient_body(  ) ;
       $this->_create_mail_body(  );

    }
    /**
     * You shouldn't need to extend this method
     * It encapsulates the sending
     */
    function send(  )
    {
        debug_push( __CLASS__, __FUNCTION__ );

        if (   $this->mail_reciept && $this->reciept->to )
        {
            if ( !$this->reciept->send(  ) )
            {
                debug_add(sprintf( "Mail to recipient failed: mail(%s, %s, %s)", $this->reciept->to, $this->reciept->subject,
                            $this->reciept->headers), MIDCOM_LOG_INFO);
                debug_pop();
                return false;
            }
           debug_add(sprintf ( "Mail to recipient %s sent SUCCESSFULLY", $this->reciept->to), MIDCOM_LOG_INFO);

        }

        if ( !$this->mail->send(  )  )
        {
            debug_add( 'Could not send email', MIDCOM_LOG_INFO );
            //debug_print_r( 'Email:', $this->mail );
        } else {
            debug_add( "Email to " . $this->mail->to . " sendt. ", MIDCOM_LOG_DEBUG );
        }
        debug_pop();
        return true;

    }
    /**
     * Creates the recipient email body.
     * extend this function if you want to have something else in the email.
     */
    function _create_recipient_body(  )
    {
        $this->reciept->body = $this->reciept_msg;
        $this->reciept->body .= $this->_create_body( );
    }

    /**
     * Creates the email to the administrator /contact handler
     * extend this function if you want to have something else in the email.
     * @return  string the email body.
     */
    function _create_mail_body(  )
    {
        $this->mail->body = $this->_create_body(  );
        $this->mail->body .= "\nMail submitted on " . strftime('%x %X', time());
        $this->mail->body .= "\nFrom IP: {$_SERVER['REMOTE_ADDR']}";
    }

    /**
     * Creates the basic email body.
     * @return string a simple formated string
     */
    function _create_body(  )
    {
        $msg = "";

        foreach ( $this->values as $key => $field)
        {
            $name = $this->schema->fields[$key]['title'];
            if (is_a ($field, 'midcom_helper_datamanager2_type_select'))
            {
                $index = $field->convert_to_storage(null);
                $value = $field->get_name_for_key($index);

            } else {
                $value = $field->convert_to_storage(null);
            }
            $msg .= sprintf( "%s: %s\n", $name , $value);

        }
        //var_dump($msg);
        //exit;
        return $msg;

    }

    /**
     * Add the receipt data
     * @param $add boolean
     */
    function set_add_reciept_data ( $add )
    {
        $this->add_reciept_data = $add;
    }
    /**
     * Set email encoding
     */
    function set_charset( $enc )
    {
        debug_pop( __FUNCTION__, __CLASS__ );
        $this->encoding = $enc;
        $this->mail->encoding = $enc;
        $ths->reciept->encoding = $enc;

        //$this->mail->headers["Content-Type"] = "text/plain; charset={$enc}";
        //$this->reciept->headers["Content-Type"] = "text/plain; charset={$enc}";
        debug_add( "Settingencoding : $enc" );
        debug_pop(  );
    }
    /**
     * Set to true if the submitter should get a receipt of the
     * email
     * @param $send boolean true if mail should be sent
     */
    function set_send_reciept( $send)
    {
        $this->mail_reciept = $send;
    }

    /**
     * Sets the receipt message
     * @param $msg string
     */
    function set_reciept_message( $msg )
    {
       $this->reciept_msg = $msg . "\n";
    }
    /**
     * The fromaddress
     * @var $from string
     */
    function set_from( $from )
    {
        $this->from = $from;
        $this->mail->headers['from'] = $from;
        $this->mail->headers['return-path'] = $from;
        $this->reciept->headers['from'] = $from;
        $this->reciept->headers['return-path'] = $from;


    }
    /**
     * Sets the reply_to address
     * @param $to string
     */
    function set_reply_to ( $to )
    {
        if ( $to )
        {
            $mail->headers['reply-to'] = $to;
        }
        // the old qf set reply-to to from. I do not to that here as you cannot know if from isset.

    }
    /**
     * @param $values array the values submitted in the form
     */
    function set_values( $values )
    {
        $this->values = $values;

        if (array_key_exists('email', $this->values))
        {
            $this->mail->from = $this->values['email']->value;
            $this->reciept->to = $this->values['email']->value;
        }
        else
        {
            $this->mail->from = $this->from->value;
        }

    }
    /**
     * @param the form schema
     */
    function set_schema (  $schema ) {
        $this->schema = $schema;
    }
    /**
     * @param string the email subject string
     */
    function set_subject( $subject , $subject_reciept)
    {
        $this->mail->headers['Subject']= $subject;
        if ( $subject_reciept == null  )
        {
            $subject_reciept = $subject;
        }
        $this->reciept->headers['Subject'] = $subject_reciept;
    }
    /**
     * This function sets the emailaddress that the submitted form will
     * be sent to.
     * @param $to string the mailaddress set in the configuration
     */
    function set_to ( $to )
    {
        $this->mail->to = $to;
        $this->mail->headers['to'] = $to;

    }

}

