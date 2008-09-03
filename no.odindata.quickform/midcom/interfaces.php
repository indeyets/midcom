<?php
/**
 * @package no.odindata.quickform
 * @author Tarjei Huse, tarjei@nu.no
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Quickform MidCOM interface class.
 *
 * @package no.odindata.quickform
 */
class no_odindata_quickform_interface extends midcom_baseclasses_components_interface
{
    function __construct()
    {
        parent::__construct();
        
        $this->_component = 'no.odindata.quickform';
        $this->_autoload_files = array
        (
            'viewer.php', 
            'navigation.php', 
            'formvar.php'
        );
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager',
            'org.openpsa.mail'
        );
    }    
}
?>