<?php
/**
 * @package org.openpsa.core
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: acl_synchronizer.php,v 1.13 2006/05/03 14:09:44 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * @ignore
 */
//These two constants are on purpose in here
define('ORG_OPENPSA_CORE_VERSION_NUMBER', '2.0.0');
define('ORG_OPENPSA_CORE_VERSION_NAME'  , 'It is all relative');

/**
 * Returns current version of OpenPsa. Three different modes are supported:
 * 1: Return version number (version name)
 * 2: Return version number
 * 3: Return version name
 * @param int $mode Mode for output
 * @return string OpenPsa version string
 */
function org_openpsa_core_version($mode = 1)
{
    switch ($mode)
    {
        case 'number':
        case 2:
            return ORG_OPENPSA_CORE_VERSION_NUMBER;
            break;
        case 'name':
        case 3:
            return ORG_OPENPSA_CORE_VERSION_NAME;
            break;
        default:
        case 'both':
        case 1:
            return ORG_OPENPSA_CORE_VERSION_NUMBER . ' (' . ORG_OPENPSA_CORE_VERSION_NAME . ')';
            break;
    }
}

?>