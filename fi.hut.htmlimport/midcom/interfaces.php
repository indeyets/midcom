<?php
/**
 * @package fi.hut.htmlimport 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for fi.hut.htmlimport
 * 
 * @package fi.hut.htmlimport
 */
class fi_hut_htmlimport_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function __construct()
    {
        parent::__construct();
        $this->_component = 'fi.hut.htmlimport';
        $this->_purecode = true;
        $this->_autoload_files = array
        (
            'importer.php'
        );
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2',
        );
    }

    function _on_initialize()
    {
        /* We might not need the full static component afterall
        $_MIDCOM->componentloader->load('net.nehmer.static');
        */
        return true;
    }

}

?>