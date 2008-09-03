<?php
/**
 * @package org.openpsa.jabber
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: admin.php,v 1.3 2005/07/04 13:05:37 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.jabber component AIS Class
 *
 * This class currently only supports the AIS Component Config interface.
 * It's the only required AIS interface as everything else is handled by
 * the OpenPSA classes/functions.
 *
 * @package org.openpsa.jabber
 */
class org_openpsa_jabber_admin extends midcom_baseclasses_components_request_admin
{
    /**
     * Constructor.
     *
     * Nothing fancy, defines the request switch to activate the component configuration.
     */
    function org_openpsa_jabber_admin($topic, $config)
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
            'schemadb' => 'file:/org/openpsa/jabber/config/schemadb_config.inc',
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