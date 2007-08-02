<?php
/**
 * @package org.maemo.socialnews 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for org.maemo.socialnews
 * 
 * @package org.maemo.socialnews
 */
class org_maemo_socialnews_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function org_maemo_socialnews_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'org.maemo.socialnews';

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'viewer.php', 
            'admin.php', 
            'navigation.php',
            'score_article.php',
            'calculator.php',
        );
        
        // Load all libraries used by component here
        $this->_autoload_libraries = array
        (
            'org.openpsa.httplib'
        );
    }

}
?>
