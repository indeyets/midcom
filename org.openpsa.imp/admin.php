<?php

/**
 * @package org.openpsa.imp
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: admin.php,v 1.2 2005/08/02 15:41:52 bergius Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.imp component AIS Class
 *
 * This class currently only supports the AIS Component Config interface.
 * It's the only required AIS interface as everything else is handled by
 * the OpenPSA classes/functions.
 *
 * @package org.openpsa.imp
 */
class org_openpsa_imp_admin extends midcom_baseclasses_components_request_admin
{
    /**
     * Constructor.
     *
     * Nothing fancy, defines the request switch to activate the component configuration.
     */
    function org_openpsa_imp_admin($topic, $config)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        parent::midcom_baseclasses_components_request_admin($topic, $config);
        debug_pop();
        return true;
    }

    function _on_initialize()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        //Match /config
        $this->_request_switch[] = Array
        (
            'fixed_arguments' => Array ('config'),
            'handler' => 'config_dm',
            'schemadb' => 'file:/org/openpsa/imp/config/schemadb_config.inc',
            'schema' => 'config',
            'disable_return_to_topic' => false,
        );

        //Match /
        $this->_request_switch[] = Array
        (
            /* These two are the default values anyway, so we can skip them. */
            // 'fixed_arguments' => null,
            // 'variable_arguments' => 0,
            'handler' => 'welcome',
        );

        debug_pop();
        return true;
    }


    function _populate_toolbar()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        //Add icon for component configuration
        $this->_topic_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'config.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));

        debug_pop();
        return true;
    }


    function _on_handler_config_dm_preparing()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $this->_populate_toolbar();

        debug_pop();
        return true;
    }

    function _handler_welcome()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $this->_populate_toolbar();

        debug_pop();
        return true;
    }

    function _show_welcome()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        midcom_show_style("admin-welcome");

        debug_pop();
        return true;
    }

}

?>