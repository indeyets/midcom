<?php

/**
 * @package pl.olga.mnogosearch
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: interfaces.php 4362 2006-10-19 15:39:43Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * p.o.mnogosearch MidCOM interface class.
 *
 *
 * @package pl.olga.mnogosearch
 */
class pl_olga_mnogosearch_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function __construct()
    {
        parent::__construct();

        $this->_component = 'pl.olga.mnogosearch';
        $this->_autoload_files = Array
        (
            'viewer.php',
        );

        $this->_autoload_libraries = Array
        (
            'midcom.helper.datamanager2',
            'midcom.helper.dm2config',
        );
    }

}
?>