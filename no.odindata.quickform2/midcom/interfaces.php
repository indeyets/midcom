<?php
/**
 * @package no.odindata.quickform2 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for no.odindata.quickform2
 * 
 * @package no.odindata.quickform2
 */
class no_odindata_quickform2_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function no_odindata_quickform2_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'no.odindata.quickform2';

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'viewer.php', 
            'navigation.php',
            'factory.php',
            'email.php',
            'emailgenerator.php',
        );
        
        // Load all libraries used by component here
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2',
            'org.openpsa.mail'
        );
    }

}
?>
