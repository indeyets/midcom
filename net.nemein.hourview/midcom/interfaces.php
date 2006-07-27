<?php

/**
 * @package net.nemein.hourview
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Net.nemein.hourview component interface class.
 * 
 * Net.nemein.hourview is a component that integrates with the OpenPSA suite.
 * Base idea is that clients could approve project hours in extranet.
 * 
 * ...
 * 
 * @package net.nemein.hourview
 */
class net_nemein_hourview_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     * 
     * Load all component files/snippets.
     */
    function net_nemein_hourview_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'net.nemein.hourview';
        $this->_autoload_files = Array('viewer.php', 'navigation.php', 'admin.php');
        $this->_autoload_libraries = Array('midcom.helper.datamanager');
    }

    /**
     * Initialize
     * 
     * Initializing the component and OpenPSA Projects interfaces.
     * We must tune down error reporting as the OpenPSA is not E_ALL compatible.
     */

    function _on_initialize()
    {   
        error_reporting(E_ALL ^ E_NOTICE);
        
        // Check that OpenPSA is new enough
        $result = mgd_include_snippet_php("/NemeinNet_Core/version");
        if (! $result)
        {
            // Snippet could not be loaded.
            return false;
        }
        
        if (!isset($GLOBALS['nemein_net']))
        {
            // No OpenPSA installed
            debug_add("OpenPSA does not seem to be installed or is too old", MIDCOM_LOG_CRIT);
            debug_pop();
            return false;
        }

        // NOTE: This does not get initialized in the Config/init, so we hardcode it here
        $GLOBALS['SNIPPET_ROOT'] = 'NemeinProjects';

        // After the following include we should have all the OpenPSA vars & etc. available
        $result = mgd_include_snippet_php("/NemeinProjects/Config/Init");
        if (! $result)
        {
            // Snippet could not be loaded.
            return false;
        }
        
        if (   !defined('__NNP_ROOTID') 
            || __NNP_ROOTID == 0)
        {
            // OpenPSA is not installed
            debug_add("OpenPSA Projects does not seem to be installed", MIDCOM_LOG_CRIT);
            debug_pop();
            return false;
        }
        
        error_reporting(E_ALL);
        
        return true;
    }
    
}

?>