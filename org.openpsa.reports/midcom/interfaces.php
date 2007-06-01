<?php
/**
 * OpenPSA Projects reporting engine
 *
 * @package org.openpsa.reports
 */
class org_openpsa_reports_interface extends midcom_baseclasses_components_interface
{

    function org_openpsa_reports_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'org.openpsa.reports';
        $this->_autoload_files = array
        (
            'viewer.php',
            'admin.php',
            'navigation.php',
            'query.php',
            'reports_handler_base.php',
        );
        $this->_autoload_libraries = array
        (
            'org.openpsa.core',
            'org.openpsa.queries',
            'org.openpsa.helpers',
            'midcom.helper.datamanager',
        );

    }

}
?>