<?php
/**
 * @package fi.hut.staticdumps 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for fi.hut.staticdumps
 * 
 * @package fi.hut.staticdumps
 */
class fi_hut_staticdumps_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function __construct()
    {
        parent::__construct();
        $this->_component = 'fi.hut.staticdumps';
        $this->_purecode = true;
        $this->_autoload_files = array
        (
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