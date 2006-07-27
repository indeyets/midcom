<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanger 2 Component Interface Class. This is a pure code library.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all core script files
     */
    function midcom_helper_datamanager2_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'midcom.helper.datamanager2';
        $this->_autoload_files = Array
        (
            'schema.php',
            'storage.php',
            'storage/midgard.php',
            'storage/null.php',
            'storage/tmp.php',
            'type.php',
            'widget.php',
            'datamanager.php',
            'formmanager.php',
            'controller.php',
        );

        // Subclasses are loaded on demand, add this to the above list for syntax checking:
        /*
            'type/text.php',
        */
    }


}

?>