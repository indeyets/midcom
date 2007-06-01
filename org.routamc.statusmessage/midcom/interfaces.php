<?php
/**
 * @package org.routamc.statusmessage 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for org.routamc.statusmessage
 * 
 * @package org.routamc.statusmessage
 */
class org_routamc_statusmessage_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function org_routamc_statusmessage_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'org.routamc.statusmessage';

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'viewer.php', 
            'admin.php', 
            'navigation.php',
            'message.php',
            'importer.php',
        );
        
        // Load all libraries used by component here
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2',
            'org.openpsa.httplib',
        );
    }

}
?>
