<?php
/**
 * @package midgard.webdav.styles 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for midgard.webdav.styles
 * 
 * @package midgard.webdav.styles
 */
class midgard_webdav_styles_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function midgard_webdav_styles_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'midgard.webdav.styles';

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'viewer.php', 
            'navigation.php',
            'handler.php',

            
        );
        
    }

}
?>
