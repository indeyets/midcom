<?php

/**
 * @package midcom.helper.search
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
 * @package midcom.helper.search
 */
class midcom_helper_search_admin extends midcom_baseclasses_components_request_admin
{
    /**
     * Constructor.
     * 
     * Nothing fancy, defines the request switch to activate the component configuration.
     */
    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);
        
        $this->_request_switch[] = Array 
        ( 
            'handler' => 'config_dm',
            'schemadb' => 'file:/midcom/helper/search/config/schemadb_config.inc',
            'disable_return_to_topic' => true
        );
    }
    
}

?>