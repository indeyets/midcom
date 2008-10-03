<?php
/**
 * @package org.openpsa.mypage
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * OpenPSA Personal Summary component
 *
 * @package org.openpsa.mypage
 */
class org_openpsa_mypage_interface extends midcom_baseclasses_components_interface
{

    function __construct()
    {
        parent::__construct();

        $this->_component = 'org.openpsa.mypage';
        $this->_autoload_files = array();
        $this->_autoload_libraries = array(
            'org.openpsa.core',
            'org.openpsa.helpers',
            'midcom.helper.datamanager2',
            'org.openpsa.contactwidget',
            'org.routamc.positioning',
        );
    }

    function _on_initialize()
    {
        // Load needed data classes
        $_MIDCOM->componentloader->load_graceful('org.openpsa.calendar');
        $_MIDCOM->componentloader->load_graceful('org.openpsa.projects');

        return true;
    }
}
?>