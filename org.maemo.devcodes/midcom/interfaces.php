<?php
/**
 * @package org.maemo.devcodes 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for org.maemo.devcodes
 * 
 * @package org.maemo.devcodes
 */
class org_maemo_devcodes_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function org_maemo_devcodes_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'org.maemo.devcodes';

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'viewer.php', 
            'navigation.php',
            'device.php',
            'code.php',
            'application.php',
        );

        // Load all libraries used by component here
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2'
        );
    }

    function _on_initialize()
    {
        define('ORG_MAEMO_DEVCODES_APPLICATION_PENDING', 4000);
        define('ORG_MAEMO_DEVCODES_APPLICATION_ACCEPTED', 4001);
        define('ORG_MAEMO_DEVCODES_APPLICATION_REJECTED', 4100);
        $_MIDCOM->componentloader->load('org.openpsa.contacts');
        return true;
    }

}
?>