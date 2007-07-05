<?php
/**
 * @package org.maemo.calendar 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for org.maemo.calendar
 * 
 * @package org.maemo.calendar
 */
class org_maemo_calendar_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function org_maemo_calendar_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'org.maemo.calendar';

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'viewer.php', 
            'admin.php', 
            'navigation.php',
            'common.php'
        );
        
		// Load all libraries used by component here
		$this->_autoload_libraries = array
		(
			'midcom.helper.datamanager2',
			'org.maemo.calendarwidget',
			'org.maemo.calendarpanel',
			'net.nemein.tag',			
		);
    }

    function _on_initialize()
    {	
		//$_MIDCOM->componentloader->load_graceful('net.nehmer.buddylist');
		$_MIDCOM->componentloader->load('org.openpsa.calendar');
        return true;
    }

}
?>
