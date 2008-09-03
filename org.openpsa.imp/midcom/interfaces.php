<?php
/**
 * @package org.openpsa.imp
 * @author Nemein Oy http://www.nemein.com/
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * OpenPSA Jabber Instant Messaging Component
 *
 * @package org.openpsa.imp
 */
class org_openpsa_imp_interface extends midcom_baseclasses_components_interface
{

    function org_openpsa_imp_interface()
    {
        parent::__construct();

        $this->_component = 'org.openpsa.imp';
        $this->_autoload_files = array(
            'viewer.php',
            'navigation.php'
        );
        $this->_autoload_libraries = array(
            /*
            'org.openpsa.core',
            'org.openpsa.helpers',
            */
            'midcom.helper.datamanager',
        );

    }

}
?>