<?php

/**
 * @package org.openpsa.mypage
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: admin.php,v 1.1 2005/07/04 12:53:47 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */
 
/**
 * org.openpsa.mypage component AIS Class
 * 
 * This class currently only supports the AIS Component Config interface.
 * It's the only required AIS interface as everything else is handled by
 * the OpenPSA classes/functions.
 * 
 * @package org.openpsa.mypage
 */
class org_openpsa_mypage_admin extends midcom_baseclasses_components_request_admin
{
    /**
     * Constructor.
     * 
     * Nothing fancy, defines the request switch to activate the component configuration.
     */
    function org_openpsa_mypage_admin($topic, $config)
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
            'schemadb' => 'file:/org/openpsa/mypage/config/schemadb_config.inc',
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