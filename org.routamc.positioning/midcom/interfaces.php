<?php
/**
 * @package org.routamc.positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Positioning library interface
 * 
 * Startup loads main class, which is used for all operations.
 * 
 * @package org.routamc.positioning
 */
class org_routamc_positioning_interface extends midcom_baseclasses_components_interface
{
    
    function org_routamc_positioning_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'org.routamc.positioning';
        $this->_purecode = true;
        $this->_autoload_files = Array(
            'importer.php',
            'object.php',
            'person.php',
            'utils.php',
            'aerodrome.php',
            'city.php',
            'location.php',
            'log.php',
        );
    }
    
    function _on_initialize()
    {
        define('ORG_ROUTAMC_POSITIONING_ACCURACY_GPS', 10);
        define('ORG_ROUTAMC_POSITIONING_ACCURACY_PLAZES', 15);
        define('ORG_ROUTAMC_POSITIONING_ACCURACY_MANUAL', 20);
        define('ORG_ROUTAMC_POSITIONING_ACCURACY_CITY', 30);

        define('ORG_ROUTAMC_POSITIONING_RELATION_IN', 10);
        define('ORG_ROUTAMC_POSITIONING_RELATION_ABOUT', 20);
        
        return true;
    }
    
    // TODO: Watchers and cron entries
}


?>