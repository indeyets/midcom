<?php
/**
 * @package midcom.helper.reflector 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for midcom.helper.reflector
 * 
 * @package midcom.helper.reflector
 */
class midcom_helper_reflector_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function midcom_helper_reflector_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'midcom.helper.reflector';
        $this->_purecode = true;

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'reflector.php', 
            'reflector_tree.php', 
        );
    }
}
?>
