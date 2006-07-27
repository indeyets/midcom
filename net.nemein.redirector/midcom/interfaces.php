<?php
/**
 * @package net.nemein.redirector
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Redirector MidCOM interface class.
 *
 * @package net.nemein.redirector
 */
class net_nemein_redirector_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_redirector_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'net.nemein.redirector';
        $this->_autoload_files = Array(
            'viewer.php',
            'admin.php',
            'navigation.php',
        );
    }
    
}

?>
