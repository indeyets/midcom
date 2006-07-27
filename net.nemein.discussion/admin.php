<?php

/**
 * @package net.nemein.discussion
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Forum AIS interface class.
 * 
 * @package net.nemein.discussion
 */
class net_nemein_discussion_admin extends midcom_baseclasses_components_request_admin
{
    function net_nemein_discussion_admin($topic, $config) 
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