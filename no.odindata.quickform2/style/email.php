<?php
/**
 * use this styleelement to process and make your emails dashing, cool and fancy!
 */
//$data =& $_MIDCOM->get_custom_context_data('request_data');

// the mail to send to the contact-recepient
$mail =&  $_MIDCOM->get_custom_context_data('mail');
// the reciept mail 
$reciept =&  $_MIDCOM->get_custom_context_data('reciept');
$form  =&  $_MIDCOM->get_custom_context_data('form');
var_dump( array_keys( $form->values(  )));
return;
        {
            if ($field == 'email' )
            {
                $email_to = $data[$field];
            }
            
            $description = $this->_l10n->get($description);
            
            if (   array_key_exists ('widget' , $schema[$this->_datamanager->get_layout_name()]['fields'][$field])
                && $schema[$this->_datamanager->get_layout_name()]['fields'][$field]['widget'] == 'radiobox')
            {
                $this->_request_data['mail']->body .= "{$description}\n";
                $this->_request_data['mail']->body .= "    ". $schema[$this->_datamanager->get_layout_name()]['fields'][$field]['widget_radiobox_choices'][$data[$field]] . "\n";
            }
            else
            {
                $this->_request_data['mail']->body .= "{$description}\n";
                $this->_request_data['mail']->body .= "    " . $data[$field] . "\n";
            }
            $this->_request_data['mail']->body .= "\n";
        }

 


