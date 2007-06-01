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
    
    function no_odindata_quickform_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'no.odindata.quickform';
        $this->_purecode = false;
        $this->_autoload_files = array
        (
            'viewer.php', 
            'navigation.php', 
            'admin.php', 
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