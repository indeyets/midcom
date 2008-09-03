<?php

/**
 * @package midgard.admin.sitewizard
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Forum AIS interface class.
 * 
 * @package midgard.admin.sitewizard
 */
class midgard_admin_sitewizard_admin extends midcom_baseclasses_components_request_admin
{
    function midgard_admin_sitewizard_admin($topic, $config) 
    {
         parent::__construct($topic, $config);
    }

    function _on_initialize()
    {
        // Configuration
        $this->_request_switch[] = Array
        (
            'handler' => 'config_dm',
            'schemadb' => 'file:/midgard/admin/sitewizard/config/schemadb_config.inc',
            'schema' => 'config',
            'disable_return_to_topic' => true,
        );    
        return true;
    }
}
?>