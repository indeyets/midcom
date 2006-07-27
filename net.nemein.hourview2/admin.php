<?php

/**
 * @package net.nemein.hourview2
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
 
/**
 * Net.nemein.hourview2 component AIS Class
 * 
 * This class currently only supports the AIS Component Config interface.
 * It's the only required AIS interface as everything else is handled by
 * the OpenPSA classes/functions.
 * 
 * @package net.nemein.hourview2
 */
class net_nemein_hourview2_admin extends midcom_baseclasses_components_request_admin
{
    /**
     * Constructor.
     * 
     * Nothing fancy, defines the request switch to activate the component configuration.
     */
    function net_nemein_hourview2_admin($topic, $config)
    {
        parent::midcom_baseclasses_components_request_admin($topic, $config);
        
        $this->_request_switch[] = Array 
        ( 
	        /* These two are the default values anyway, so we can skip them. */
	        // 'fixed_arguments' => null,
	        // 'variable_arguments' => 0,
	        'handler' => 'welcome'
        );
        
        $this->_request_switch[] = Array 
        ( 
            'fixed_arguments' => Array ('config'),
            'handler' => 'config_dm',
            'schemadb' => 'file:/net/nemein/hourview2/config/schemadb_config.inc',
            'disable_return_to_topic' => true
        );
    }
    
    function _handler_welcome()
    {
        return true;
    }
    
    function _show_welcome()
    {
        midcom_show_style("admin-welcome");
        return true;
    }
    
}

?>