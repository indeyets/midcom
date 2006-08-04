<?php
/**
 * Send backend for org_openpsa_mail, using jzs bounce detection system
 * in fa t the bounce detect can work both with mail_smtp and mail_sendmail
 * but to simplify usage we wrap both here into this "meta backend"
 * @package org.openpsa.mail
 */
class org_openpsa_mail_backend_bouncer
{
    var $error = false;
    var $_try_backends = array('mail_smtp', 'mail_sendmail'); //Backends that properly set the ENVELOPE address from "Return-Path" header
    var $_backend = null;

    function org_openpsa_mail_backend_bouncer()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        foreach ($this->_try_backends as $backend)
        {
            debug_add("Trying backend {$backend}");
            if (   $this->_load_backend($backend)
                && $this->_backend->is_available())
            {
                debug_add('OK');
                break;
            }
            debug_add("backend {$backend} is not available");
        }
        debug_pop();
        return true;
    }

    function send(&$mailclass, &$params)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (   !$this->is_available()
            || !is_object($this->_backend)
            || !method_exists($this->_backend, 'send'))
        {
            debug_add('backend is unavailable');
            $this->error = 'Backend is unavailable';
            debug_pop();
            return false;
        }
        debug_pop();
        $mailclass->headers['X-org.openpsa.mail-bouncer-backend-class'] = get_class($this->_backend);

        return $this->_backend->send($mailclass, $params);
    }

    function get_error_message()
    {
        if (   is_object($this->_backend)
            && method_exists($this->_backend, 'get_error_message'))
        {
            return $this->_backend->get_error_message();
        }
        if (!$this->error)
        {
            return false;
        }
        if (!empty($this->error))
        {
            return $this->error;
        }
        return 'Unknown error';
    }
    
    function is_available()
    {
        if (   !is_object($this->_backend)
            || !method_exists($this->_backend, 'is_available'))
        {
            return false;
        }
        return $this->_backend->is_available();
    }

    function _load_backend($backend)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $classname = "org_openpsa_mail_backend_{$backend}";
        if (class_exists($classname))
        {
            $this->_backend = new $classname();
            debug_add("backend is now\n===\n" . sprint_r($this->_backend) . "===\n");
            debug_pop();
            return true;
        }
        debug_add("backend class {$classname} is not available", MIDCOM_LOG_WARN);
        return false;
    }
} 

?>