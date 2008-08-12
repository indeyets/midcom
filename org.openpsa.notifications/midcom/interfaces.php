<?php
/**
 * OpenPSA notifications manager
 *
 * Startup loads main class, which is used for all operations.
 *
 * @package org.openpsa.notifications
 * @author Henri Bergius, http://bergie.iki.fi
 * @version $Id: interfaces.php,v 1.1 2006/05/24 16:01:00 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package org.openpsa.notifications
 */
class org_openpsa_notifications_interface extends midcom_baseclasses_components_interface
{
    /**
     * Initializes the library and loads needed files
     */
    function org_openpsa_notifications_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'org.openpsa.notifications';
    }
}
?>