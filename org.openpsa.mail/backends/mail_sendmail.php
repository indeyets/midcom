<?php
/**
 * Send backend for org_openpsa_mail, using PEAR Mail_sendmail
 * @package org.openpsa.mail
 */
class org_openpsa_mail_backend_mail_sendmail
{
    var $error = false;
    var $_mail = null;

    function org_openpsa_mail_backend_mail_sendmail()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('constructor called');
        if (!class_exists('Mail')) 
        {
            debug_add('class "Mail" not found trying to include Mail.php');
            @include_once('Mail.php');
        }
        if (   class_exists('Mail')
            && !class_exists('Mail_sendmail'))
        {
            debug_add('class "Mail_sendmail" not found trying to include Mail/smtp.php');
            @include_once('Mail/sendmail.php');
        }
        debug_pop();
        return true;
    }

    function send(&$mailclass, &$params)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!$this->is_available())
        {
            debug_add('backend is unavailable');
            $this->error = 'Backend is unavailable';
            debug_pop();
            return false;
        }
        if (!is_array($params))
        {
            $params = array();
        }
        if ($mailclass->_config->get('sendmail_path'))
        {
            $params['sendmail_path'] = $mailclass->_config->get('sendmail_path');
        }
        if ($mailclass->_config->get('sendmail_args'))
        {
            $params['sendmail_args'] = $mailclass->_config->get('sendmail_args');
        }

        $this->_mail = Mail::factory('sendmail', $params);
        $mail =& $this->_mail;
        $mailRet = $mail->send($mailclass->merge_address_headers(), $mailclass->headers, $mailclass->body);
        debug_add("mail->send returned\n===\n" . sprint_r($mailRet) . "===\n");
        if ($mailRet === true)
        { 
            $ret = true;
            $this->error = false;
        }
        else
        { 
            $ret = false;
            $this->error = $mailRet;
        }

        debug_pop();
        return $ret;
    }

    function get_error_message()
    {
        if ($this->error === false)
        {
            return false;
        }
        $errObj =& $this->error;
        if (is_object($errObj))
        {
            return $errObj->getMessage();
        }
        if (!empty($this->error))
        {
            return $this->error;
        }
        return 'Unknown error';
    }

    function is_available()
    {
        return class_exists('Mail_sendmail');
    }
} 

?>