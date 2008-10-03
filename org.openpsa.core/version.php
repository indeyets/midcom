<?php
/**
 * @package org.openpsa.core
 * @author Nemein Oy http://www.nemein.com/
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
 *  version number (version name)
 *  version number
 *  version name
 */
class org_openpsa_core_version
{
    /**
     * Returns version number
     *  
     * @return string OpenPsa version string
     */
    function get_version_number()
    {
            return ORG_OPENPSA_CORE_VERSION_NUMBER;
    }

    /**
     * Returns version name
     *  
     * @return string OpenPsa version string
     */
    function get_version_name()
    {
            return ORG_OPENPSA_CORE_VERSION_NAME;
    }

    /**
     * Returns version number and name
     *  
     * @return string OpenPsa version string
     */
    function get_version_both()
    {
      return ORG_OPENPSA_CORE_VERSION_NUMBER . ' (' . ORG_OPENPSA_CORE_VERSION_NAME . ')';
    }
}
?>