<?php
/**
 * @package org.openpsa.reports
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

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