<?php
/**
 * @package net.nemein.flashplayer
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for net.nemein.flashplayer
 * 
 * @package net.nemein.flashplayer
 */
class net_nemein_flashplayer_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     */
    function __construct()
    {
        parent::__construct();

        $this->_component = 'net.nemein.flashplayer';
        $this->_purecode = true;
        
        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
        );
    }

    function _on_initialize()
    {
        return true;
    }

}
?>