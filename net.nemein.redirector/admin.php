<?php
/**
 * @package net.nemein.redirector
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Redirector Admin interface class.
 * 
 * @package net.nemein.redirector
 */
class net_nemein_redirector_admin extends midcom_baseclasses_components_request_admin
{

    function net_nemein_redirector_admin($topic, $config) 
    {
        parent::__construct($topic, $config);
    }

    function _on_initialize()
    {     
        // Configuration
        $this->_request_switch[] = Array
        (
            'handler' => 'config_dm',
            'schemadb' => 'file:/net/nemein/redirector/config/schemadb_config.inc',
            'schema' => 'config',
            'disable_return_to_topic' => true,
        );
    }
}
?>