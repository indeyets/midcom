<?php
/**
 * @package midcom.helper.filesync 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for midcom.helper.filesync
 * 
 * @package midcom.helper.filesync
 */
class midcom_helper_filesync_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function midcom_helper_filesync_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'midcom.helper.filesync';

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'exporter.php',
        );
        
        // Load all libraries used by component here
        $this->_autoload_libraries = array();
    }

}
?>
