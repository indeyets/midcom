<?php
/**
 * OpenPSA Jabber Instant Messaging Component
 *
 * @package org.openpsa.imp
 */
class org_openpsa_imp_interface extends midcom_baseclasses_components_interface
{

    function org_openpsa_imp_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'org.openpsa.imp';
        $this->_autoload_files = array(
            'viewer.php',
            'navigation.php'
        );
        $this->_autoload_libraries = array(
            /*
            'org.openpsa.core',
            'org.openpsa.helpers',
            */
            'midcom.helper.datamanager',
        );

    }

}
?>