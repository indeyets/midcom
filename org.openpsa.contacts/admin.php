<?php
/**
 * @package org.openpsa.contacts
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: admin.php,v 1.1 2005/05/24 17:56:04 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.contacts component AIS Class
 *
 * This class currently only supports the AIS Component Config interface.
 * It's the only required AIS interface as everything else is handled by
 * the OpenPSA classes/functions.
 *
 * @package org.openpsa.contacts
 */
class org_openpsa_contacts_admin extends midcom_baseclasses_components_request_admin
{
    /**
     * Constructor.
     *
     * Nothing fancy, defines the request switch to activate the component configuration.
     */
    function __construct($topic, $config)
    {
        parent::midcom_baseclasses_components_request_admin($topic, $config);

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
            'schemadb' => 'file:/org/openpsa/contacts/config/schemadb_config.inc',
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