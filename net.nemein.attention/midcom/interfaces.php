<?php
/**
 * @package net.nemein.attention 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for net.nemein.attention
 * 
 * @package net.nemein.attention
 */
class net_nemein_attention_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_attention_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'net.nemein.attention';

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'click.php',
            'concept.php',
            'exporter.php',
            'importer.php',
            'source.php',
        );
    }

}
?>
