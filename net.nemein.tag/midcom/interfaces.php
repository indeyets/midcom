<?php
/**
 * @package net.nemein.tag 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Tag handling library interface class
 * 
 * @package net.nemein.tag
 */
class net_nemein_tag_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_tag_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'net.nemein.tag';
        $this->_purecode = true;
        $this->_autoload_files = array
        (
            'handler.php',
            'tag.php',
            'tag_link.php',
        );
    }

}
?>
