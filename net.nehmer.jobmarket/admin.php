<?php
/**
 * @package net.nehmer.jobmarket
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Job Market AIS interface class
 *
 * @package net.nehmer.jobmarket
 */

class net_nehmer_jobmarket_admin extends midcom_baseclasses_components_request_admin
{
    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);
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
            'schemadb' => 'file:/net/nehmer/jobmarket/config/schemadb_config.inc',
            'schema' => 'config',
            'disable_return_to_topic' => false
        );
    }
}

?>