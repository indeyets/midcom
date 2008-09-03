<?php
/**
 * @package midgard.admin.sitewizard
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Midgard Site Wizard interface
 *
 * @package midgard.admin.sitewizard
 */
class midgard_admin_sitewizard_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function __construct()
    {
        parent::__construct();

        $this->_component = 'midgard.admin.sitewizard';
        $this->_autoload_files = array
        (
            'viewer.php', 
            'navigation.php'
        );
        $this->_autoload_libraries = array
        (
            'midgard.admin.sitegroup',
            'midcom.helper.xml',
        );
    }
}
?>