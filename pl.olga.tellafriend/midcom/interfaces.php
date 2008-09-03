<?php
/**
 * @package pl.olga.tellafriend
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: interfaces.php 918 2005-04-19 06:56:24Z torben $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package pl.olga.tellafriend
 */
class pl_olga_tellafriend_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function pl_olga_tellafriend_interface()
    {
        parent::__construct();

    $this->purecode = true;
        $this->_component = 'pl.olga.tellafriend';
        
        $this->_autoload_files = Array(
            'viewer.php'
        );
        
        $this->_autoload_libraries = Array(
            'midcom.helper.datamanager2',
            'org.openpsa.mail'
        );
    }

}

?>