<?php
/**
 * @package org.openpsa.interviews
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: admin.php,v 1.1 2006/05/08 11:22:49 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Phone interview Admin interface class.
 * 
 * @package org.openpsa.interviews
 */
class org_openpsa_interviews_admin extends midcom_baseclasses_components_request_admin
{

    function org_openpsa_interviews_admin($topic, $config) 
    {
        parent::midcom_baseclasses_components_request_admin($topic, $config);
    }

    function _on_initialize()
    {     
        // Configuration
        $this->_request_switch[] = Array
        (
            'handler' => 'config_dm',
            'schemadb' => 'file:/org/openpsa/interviews/config/schemadb_config.inc',
            'schema' => 'config',
            'disable_return_to_topic' => true,
        );
    }
}
?>