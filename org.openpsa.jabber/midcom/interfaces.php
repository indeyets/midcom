<?php
/**
 * OpenPSA Jabber Instant Messaging Component
 *
 * @package org.openpsa.jabber
 */
class org_openpsa_jabber_interface extends midcom_baseclasses_components_interface
{

    function org_openpsa_jabber_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'org.openpsa.jabber';
        $this->_autoload_files = array(
            'viewer.php',
            'admin.php',
            'navigation.php'
        );
        $this->_autoload_libraries = array(
            'org.openpsa.core',
            'org.openpsa.helpers'
        );

    }

}
?>