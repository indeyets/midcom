<?php
/**
 * Send backend for org_openpsa_mail, using PHPs mail() function
 * @package org.openpsa.mail
 */
class org_openpsa_mail_backend_mail
{
    var $error = false;

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
        $hdr = '';
        reset($mailclass->headers);
        foreach ($mailclass->headers as $k => $v)
        {
            $hdr .= "{$k}: {$v}\n";
        }
        $ret = mail($mailclass->merge_address_headers(), $mailclass->subject, $mailclass->body, $hdr);
        if (!$ret)
        { 
            $this->error = true;
        }
        else
        {
            $this->error = false;
        }
        return $ret;
    }
    
    function get_error_message()
    {
        if (!$this->error)
        {
            return false;
        }
        return 'Unknown error';
    }

    function is_available()
    {
        return function_exists('mail');
    }
} 

?>