<?php

/**
 * @package net.nemein.internalorders
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: admin.php,v 1.7.2.6 2005/11/07 18:57:41 bergius Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Calendar Admin interface class.
 * 
 * @package net.nemein.internalorders
 */

class net_nemein_internalorders_admin extends midcom_baseclasses_components_request_admin
{
    function __construct($topic, $config) 
    {
        parent::__construct($topic, $config);
    }

    /**
     * The initialization tries to load the root event and will create one if
     * it couldn't be found. It will also load the Datamanager schema database,
     * which is used here and there for the toolbars etc.
     * 
     * @access private
     */
    function _on_initialize()
    {
        // Set up the URL space        
        $this->_request_switch[] = Array
        (
            'handler' => 'config_dm',
            'fixed_args' => array
            (
                'config'
            ),
            'schemadb' => 'file:/net/nemein/internalorders/config/schemadb_config.inc',
            'schema' => 'config',
            'disable_return_to_topic' => true
        );
        
        return true;                 
    }
}
?>