<?php
/**
 * @package org.openpsa.documents
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: admin.php,v 1.1 2005/06/09 17:05:17 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.documents component AIS Class
 *
 * This class currently only supports the AIS Component Config interface.
 * It's the only required AIS interface as everything else is handled by
 * the OpenPSA classes/functions.
 *
 * @package org.openpsa.documents
 */
class org_openpsa_documents_admin extends midcom_baseclasses_components_request_admin
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
            /* These two are the default values anyway, so we can skip them. */
            // 'fixed_arguments' => null,
            // 'variable_arguments' => 0,
            'handler' => 'welcome'
        );

        $this->_request_switch[] = Array
        (
            'fixed_arguments' => Array ('config'),
            'handler' => 'config_dm',
            'schemadb' => 'file:/org/openpsa/documents/config/schemadb_config.inc',
            'disable_return_to_topic' => true
        );
    }

    /**
     * @return boolean Indicating success.
     */
    function _handler_welcome()
    {
        return true;
    }

    function _show_welcome()
    {
        midcom_show_style("admin-welcome");
        return true;
    }

}

?>