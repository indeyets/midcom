<?php
/**
 * @package org.openpsa.mail
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Send backend for org_openpsa_mail, using PEAR Mail_smtp
 * @package org.openpsa.mail
 */
class org_openpsa_mail_backend_mail_smtp
{
    var $error = false;
    var $_mail = null;

    function __construct()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('constructor called');
        if (!class_exists('Mail'))
        {
            debug_add('class "Mail" not found trying to include Mail.php');
            @include_once('Mail.php');
        }
        if (   class_exists('Mail')
            && !class_exists('Mail_smtp'))
        {
            debug_add('class "Mail_smtp" not found trying to include Mail/smtp.php');
            @include_once('Mail/smtp.php');
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
        if ($mailclass->_config->get('smtp_host'))
        {
            $params['host'] = $mailclass->_config->get('smtp_host');
        }
        if ($mailclass->_config->get('smtp_port'))
        {
            $params['port'] = $mailclass->_config->get('smtp_port');
        }

        $this->_mail = Mail::factory('smtp', $params);
        $mail =& $this->_mail;
        $merged = $mailclass->merge_address_headers();
        //debug_add("address_headers_merged:\n===\n{$merged}\n===\nheaders:\n===\n" . sprint_r($mailclass->headers) . "===\n");
        $mailRet = $mail->send($merged, $mailclass->headers, $mailclass->body);
        //This gives *huge* log in case of error since the full org_openpsa_mail object is included in the PEAR error as well
        //debug_add("mail->send returned\n===\n" . sprint_r($mailRet) . "===\n");
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
        return class_exists('Mail_smtp');
    }
}

?>