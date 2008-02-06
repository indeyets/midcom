<?php
/**
 * @package net.nemein.hourview2
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Net.nemein.hourview2 site interface class.
 * 
 * Display a list of un-approved hours limited to a certain company.
 * Also provide a form to approve those hours according to selections
 * made with checkboxes.
 * 
 * ...
 * 
 * @package net.nemein.hourview2
 */
class net_nemein_hourview2_viewer extends midcom_baseclasses_components_request
{
    /**
     * Constructor.
     * 
     * Nothing fancy, defines the request switch.
     */
    function net_nemein_hourview2_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);      
    }
    
    function _on_initialize()
    {
        // Always run in uncached mode
        $_MIDCOM->cache->content->no_cache();
        
        $this->_request_switch['list_unapproved'] = Array 
        ( 
            'handler' => array('net_nemein_hourview2_handler_view', 'index'),
        );
        
        $this->_request_switch['list_all'] = Array 
        ( 
            'handler' => array('net_nemein_hourview2_handler_view', 'index'),
            'fixed_args' => Array('all'),
        );
    }
}

?>