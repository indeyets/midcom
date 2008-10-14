<?php
/**
 * OpenPSA helpers library, helpers used around OpenPSA.
 *
 * @package org.openpsa.helpers
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package org.openpsa.helpers
 */
class org_openpsa_helpers_interface extends midcom_baseclasses_components_interface
{

    function __construct()
    {
        parent::__construct();

        $this->_component = 'org.openpsa.helpers';
        $this->_purecode = true;
        $this->_autoload_files = array();
    }
}


?>