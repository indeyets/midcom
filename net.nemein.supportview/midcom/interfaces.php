<?php
/**
 * @package net.nemein.supportview
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * OpenPSA Supporview MidCOM interface class.
 * 
 * @package net.nemein.supportview
 */
class net_nemein_supportview_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     * 
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_supportview_interface()
    {
        parent::__construct();
        
        $this->_component = 'net.nemein.supportview';
        $this->_autoload_files = Array('viewer.php', 'navigation.php');
        $this->_autoload_libraries = Array('midcom.helper.datamanager');
    }
    
    function _on_initialize()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        // TODO: OpenPSA 1.x Support module doesn't run without user, fix the app itself
        if (!$GLOBALS["midgard"]->user)
        {
            debug_add('OpenPSA 1.x Support module does not run without user', MIDCOM_LOG_CRIT);
            debug_pop();
            return false;
        }
    
        // include code snippets
        $prefix = MIDCOM_ROOT . "/net/nemein/supportview";
        $techsupport_config = mgd_get_topic_by_name(0, "__TechSupport_Config");
        if (!$techsupport_config) 
        {
            debug_add("OpenPSA Support is not configured for the Sitegroup, aborting", MIDCOM_LOG_CRIT);
            debug_pop();
            return false;
        }
        require("{$prefix}/helper.php");
        
        debug_pop();
        return true;
    }
}

?>