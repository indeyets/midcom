<?php
/**
 * @package org.openpsa.sales
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: admin.php,v 1.1 2006/05/08 14:07:19 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Phone interview Admin interface class.
 *
 * @package org.openpsa.sales
 */
class org_openpsa_sales_admin extends midcom_baseclasses_components_request_admin
{

    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);
    }

    function _on_initialize()
    {
        // Configuration
        $this->_request_switch[] = Array
        (
            'handler' => 'config_dm',
            'schemadb' => 'file:/org/openpsa/sales/config/schemadb_config.inc',
            'schema' => 'config',
            'disable_return_to_topic' => true,
        );
    }
}
?>