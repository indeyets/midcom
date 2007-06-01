<?php
/**
* @package fi.protie.garbagetruck
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * Interfaces class for fi.protie.garbagetruck
 * 
 * @package fi.protie.garbagetruck
 */
class fi_protie_garbagetruck_interface extends midcom_baseclasses_components_interface
{
    /** 
     * Array containing the files that should be autoloaded
     * 
     * @var Array
     */
    var $_autoload_files = array ();
    
    /** 
     * Array containing the libraries that should be autoloaded
     * 
     * @var Array
     * @access private
     */
    var $_autoload_libraries = array ();
    
    /** 
     * Name of the component
     * 
     * @var string
     * @access private
     */
    var $_component = 'fi.protie.garbagetruck';
    
    /**
     * Simple constructor, which autoloads the required files and libraries.
     * 
     * @access protected
     */
    function fi_protie_garbagetruck_interface ()
    {
        parent::midcom_baseclasses_components_interface();
        
        define('FI_PROTIE_GARBAGETRUCK_LEAFID_LOG', 1);
        define('FI_PROTIE_GARBAGETRUCK_LEAFID_AREA', 2);
        define('FI_PROTIE_GARBAGETRUCK_LEAFID_ROUTE', 3);
        define('FI_PROTIE_GARBAGETRUCK_LEAFID_VEHICLE', 4);

        $this->_autoload_files = array
        (
            'viewer.php',
            'navigation.php',
            'admin.php',
            'dba_classes/log_dba.php',
            'dba_classes/area_dba.php',
            'dba_classes/route_dba.php',
            'dba_classes/vehicle_dba.php',
        );
        
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2',
        );
    }
}
?>