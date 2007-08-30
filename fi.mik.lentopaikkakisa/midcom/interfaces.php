<?php

/**
 * @package fi.mik.lentopaikkakisa
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Flight competition MidCOM interface class.
 * 
 * @package fi.mik.lentopaikkakisa
 */
class fi_mik_lentopaikkakisa_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     * 
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function fi_mik_lentopaikkakisa_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'fi.mik.lentopaikkakisa';
        $this->_autoload_files = Array
        (
            'viewer.php', 
            'navigation.php',
            'flight.php',
        );
        $this->_autoload_libraries = array
        (
            'org.openpsa.qbpager',
            'org.routamc.positioning',
        );
    }
    
    function _on_initialize()
    {
        //With the plentyness of typecasting around any other numeric locale calls for trouble with floats
        setlocale(LC_NUMERIC, 'C');
        
        $_MIDCOM->componentloader->load_graceful('org.openpsa.calendar');
        $_MIDCOM->componentloader->load_graceful('org.openpsa.contacts');        
        
        return true;
    }
}
?>