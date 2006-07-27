<?php
/**
 * @package net.nehmer.buddylist
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Marketplace AIS interface class
 *
 * @package net.nehmer.buddylist
 */

class net_nehmer_buddylist_admin extends midcom_baseclasses_components_request_admin
{
    function net_nehmer_buddylist_admin($topic, $config)
    {
        parent::midcom_baseclasses_components_request_admin($topic, $config);
    }

    /**
     * @access private
     */
    function _on_initialize()
    {
        // Configuration
        $this->_request_switch[] = Array
        (
            'handler' => 'config_dm',
            // 'fixed_args' => Array('config'),
            'schemadb' => 'file:/net/nehmer/buddylist/config/schemadb_config.inc',
            'schema' => 'config',
            'disable_return_to_topic' => false
        );
    }
}

?>
