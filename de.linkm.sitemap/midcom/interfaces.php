<?php
/**
 * @package de.linkm.sitemap
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Sitemap MidCOM interface class.
 * 
 * @package de.linkm.sitemap
 */

class de_linkm_sitemap_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     * 
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function __construct()
    {
        parent::__construct();
        
        $this->_component = 'de.linkm.sitemap';
        $this->_autoload_files = array
        (
            'viewer.php',
            'navigation.php'
        );
    }
}

?>