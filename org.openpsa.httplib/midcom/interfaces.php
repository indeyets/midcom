<?php
/**
 * @package org.openpsa.httplib
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package org.openpsa.httplib
 */
class org_openpsa_httplib_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function __construct()
    {
        parent::__construct();
        $this->_component = 'org.openpsa.httplib';
        $this->_purecode = true;
        $this->_autoload_files = array
        (
            'helpers.php',
            'main.php',
            'Snoopy.php',
            'hkit.php',
        );
    }

}
?>