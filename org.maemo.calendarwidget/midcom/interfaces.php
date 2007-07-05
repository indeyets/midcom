<?php
/**
 * @package org.maemo.calendarwidget 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for org.maemo.calendarwidget
 * 
 * @package org.maemo.calendarwidget
 */
class org_maemo_calendarwidget_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function org_maemo_calendarwidget_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'org.maemo.calendarwidget';
		$this->_purecode = true;
		
        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'main.php',
//            'event.php',
        );
    }

    function _on_initialize()
    {
        // Constants for the rendering styles we need
        if (!defined('ORG_MAEMO_CALENDARWIDGET_YEAR'))
        {
            define('ORG_MAEMO_CALENDARWIDGET_YEAR', 1);
        }
        if (!defined('ORG_MAEMO_CALENDARWIDGET_MONTH'))
        {
            define('ORG_MAEMO_CALENDARWIDGET_MONTH', 2);
        }
        if (!defined('ORG_MAEMO_CALENDARWIDGET_WEEK'))
        {
            define('ORG_MAEMO_CALENDARWIDGET_WEEK', 3);
        }
        if (!defined('ORG_MAEMO_CALENDARWIDGET_DAY'))
        {
            define('ORG_MAEMO_CALENDARWIDGET_DAY', 4);
        }

        // Resource types
        if (!defined('ORG_MAEMO_CALENDARWIDGET_RESOURCE_PERSON'))
        {
            define('ORG_MAEMO_CALENDARWIDGET_RESOURCE_PERSON', 5);
        }
        if (!defined('ORG_MAEMO_CALENDARWIDGET_RESOURCE_MISC'))
        {
            define('ORG_MAEMO_CALENDARWIDGET_RESOURCE_MISC', 6);
        }

        return true;
    }

}
?>
