<?php
/**
 * @package pl.olga.windguru
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */


/** @ ignore */
define('WG_STATUS_NONE',100);
define('WG_STATUS_GFS',3);
define('WG_STATUS_NWW3',10);

/**
 * This is the interface class for pl.olga.windguru
 *
 * @package pl.olga.windguru
 */
class pl_olga_windguru_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function __construct()
    {
        parent::__construct();
        $this->_component = 'pl.olga.windguru';

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'viewer.php',
            'navigation.php',
            'windguru.php',
            'dba.php',
        );

        // Load all libraries used by component here
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2',
            'midcom.helper.dm2config',
        );
    }

}
?>