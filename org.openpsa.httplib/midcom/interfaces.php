<?php

/**
 * @package org.openpsa.httplib
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

class org_openpsa_httplib_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function org_openpsa_httplib_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'org.openpsa.httplib';
        $this->_purecode = true;
        $this->_autoload_files = array
        (
            'helpers.php',
            'main.php',
            'Snoopy.php',
        );
        
        if (version_compare(phpversion(), '5.0.0', '>=')) 
        {
            $this->_autoload_files[] = 'hkit.php';
        }
        
        $this->_autoload_libraries = array(
            'org.openpsa.core',
        );
    }

}
?>