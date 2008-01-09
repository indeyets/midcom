<?php
/**
 * OpenPSA contact widget for displaying a contact person as hCard
 * 
 * Startup loads main class, which is used for all operations.
 * 
 * @package net.nemein.repeathandler
 * @author Henri Bergius, http://bergie.iki.fi
 * @version $Id$
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package net.nemein.repeathandler
 */
class net_nemein_repeathandler_interface extends midcom_baseclasses_components_interface
{
    /**
     * Initializes the library and loads needed files
     */
    function net_nemein_repeathandler_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'net.nemein.repeathandler';
        $this->_purecode = true;
        $this->_autoload_files = Array(
            'handler.php',
            'calculator.php',
        );
    }
}
?>