<?php
/**
 * @package fi.hut.loginbroker 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for fi.hut.loginbroker
 * 
 * @package fi.hut.loginbroker
 */
class fi_hut_loginbroker_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function fi_hut_loginbroker_interface()
    {
        parent::__construct();
        $this->_component = 'fi.hut.loginbroker';
        $this->_purecode = false;
        $this->_autoload_files = array
        (
            'callbacks/prototypes.php',
            'viewer.php',
            'navigation.php',
        );
        $this->_autoload_libraries = array
        (
        );
    }

    function _on_initialize()
    {
        return true;
    }

}

?>