<?php
/**
 * OpenPSA mail library, handles encoding/sending and decoding.
 *
 * Startup loads main class, which is used for all operations.
 *
 * @package org.openpsa.mail
 */
class org_openpsa_mail_interface extends midcom_baseclasses_components_interface
{

    function org_openpsa_mail_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'org.openpsa.mail';
        $this->_purecode = true;
        $this->_autoload_files = Array(
            'main.php',
            'backends/mail.php',
            'backends/mail_smtp.php',
            'backends/mail_sendmail.php',
            'backends/bouncer.php',
        );
        $this->_autoload_libraries = array(
            'org.openpsa.helpers',
        );
    }

    function _on_initialize()
    {
        return true;
    }
}


?>