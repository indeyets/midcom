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

    function __construct()
    {
        parent::__construct();

        $this->_component = 'org.openpsa.reports';
        $this->_autoload_files = array();
        $this->_autoload_libraries = array
        (
            'org.openpsa.core',
        );

    }

}
?>