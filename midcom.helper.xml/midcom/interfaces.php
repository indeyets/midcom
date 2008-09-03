<?php
/**
 * @package midcom.helper.xml
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * XML Component Interface Class. This is a pure code library.
 * 
 * @package midcom.helper.xml
 */
class midcom_helper_xml_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     */
    function __construct()
    {
        parent::__construct();
        
        $this->_component = 'midcom.helper.xml';
        $this->_autoload_files = Array
        (
            'toarray.php',
            'objectmapper.php'
        );      
    }
}

?>