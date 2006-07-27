<?php

/**
 * @package midcom.helper.helloworld
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM Indexer Front-End, AIS Class
 * 
 * This class currently only supports the AIS Component Config interface.
 * 
 * @package midcom.helper.helloworld
 */
class midcom_helper_helloworld_admin extends midcom_baseclasses_components_request_admin
{
    /**
     * Constructor.
     * 
     * Nothing fancy, defines the request switch to activate the component configuration.
     */
    function midcom_helper_helloworld_admin($topic, $config)
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
            'schemadb' => 'file:/midcom/helper/helloworld/config/schemadb_config.inc',
            'disable_return_to_topic' => true
        );
    }
    
}

?>