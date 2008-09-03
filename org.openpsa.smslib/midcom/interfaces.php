<?php
/**
 * @package org.openpsa.smslib
 */

/**
 * OpenPSA SMS library, handles sending SMS/MMS
 *
 * @package org.openpsa.smslib
 */
class org_openpsa_smslib_interface extends midcom_baseclasses_components_interface
{
    function org_openpsa_smslib_interface()
    {
        parent::__construct();

        $this->_component = 'org.openpsa.smslib';
        $this->_purecode = true;
        $this->_autoload_files = Array(
            'factory.php',
            'tambur.php',
            'clickatell.php',
            'messto.php',
            'email2sms.php',
        );
    }
}


?>