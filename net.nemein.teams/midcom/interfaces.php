<?php
/**
 * @package net.nemein.team 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for net.nemein.teams
 * 
 * @package net.nemein.teams
 */
class net_nemein_teams_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_teams_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'net.nemein.teams';

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'viewer.php', 
            //'admin.php', 
            'navigation.php',
	    'log.php',
	    'logger.php',
	    'team.php'
        );
        
        // Load all libraries used by component here
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2'
        );
    }
}
?>
