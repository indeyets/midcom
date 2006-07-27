<?php

/**
 * @package fi.mik.lentopaikkakisa
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Forum AIS interface class.
 * 
 * @package fi.mik.lentopaikkakisa
 */
class fi_mik_lentopaikkakisa_admin extends midcom_baseclasses_components_request_admin
{
    function fi_mik_lentopaikkakisa_admin($topic, $config) 
    {
         parent::midcom_baseclasses_components_request_admin($topic, $config);
    }

    function _on_initialize()
    {
        // Configuration
        $this->_request_switch[] = Array
        (
            'handler' => 'config_dm',
            'schemadb' => 'file:/net/nemein/discussion/config/schemadb_config.inc',
            'schema' => 'config',
            'disable_return_to_topic' => true,
        );    
        return true;
    }
}
?>