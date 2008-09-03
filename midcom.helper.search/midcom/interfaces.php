<?php

/**
 * @package midcom.helper.search
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM Indexer Front-End
 * 
 * No Reindex-Driver, as this component does not have anything to index.
 * 
 * @package midcom.helper.search
 */
class midcom_helper_search_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     * 
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function midcom_helper_search_interface()
    {
        parent::__construct();
        
        $this->_component = 'midcom.helper.search';
        $this->_autoload_files = Array('viewer.php', 'navigation.php');
        $this->_autoload_libraries = Array('midcom.helper.datamanager');
    }
    
}

?>