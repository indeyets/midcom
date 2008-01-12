<?php
/**
 * @package org.openpsa.directmarketing
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: viewer.php,v 1.18 2006/07/06 15:48:43 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.directmarketing site interface class.
 *
 * Direct marketing and mass mailing lists
 *
 * @package org.openpsa.directmarketing
 */
class org_openpsa_directmarketing_viewer extends midcom_baseclasses_components_request
{
    var $_datamanagers = array();
    var $_campaign_handler = null;
    var $_message_handler = null;
    var $_logger_handler = null;
    var $_toolbars;

    function org_openpsa_directmarketing_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);

        $this->_toolbars =& midcom_helper_toolbars::get_instance();

        // Load datamanagers for main classes
        //$this->_initialize_datamanager('campaign', $this->_config->get('schemadb_campaign'));
        $this->_initialize_datamanager('message', $this->_config->get('schemadb_message'));

        // Always run in uncached mode
        $_MIDCOM->cache->content->no_cache();

        // Match /campaign/import/<guid>
        $this->_request_switch[] = array(
            'fixed_args' => Array('campaign', 'import'),
            'variable_args' => 1,
            'handler' => Array('org_openpsa_directmarketing_handler_import', 'index'),
        );

        // Match /campaign/import/simpleemails/<guid>
        $this->_request_switch[] = array(
            'fixed_args' => Array('campaign', 'import', 'simpleemails'),
            'variable_args' => 1,
            'handler' => Array('org_openpsa_directmarketing_handler_import', 'simpleemails'),
        );

        // Match /campaign/import/csv/<guid>
        $this->_request_switch[] = array(
            'fixed_args' => Array('campaign', 'import', 'csv'),
            'variable_args' => 1,
            'handler' => Array('org_openpsa_directmarketing_handler_import', 'csv_select'),
        );

        // Match /campaign/import/csv2/<guid>
        $this->_request_switch[] = array(
            'fixed_args' => Array('campaign', 'import', 'csv2'),
            'variable_args' => 1,
            'handler' => Array('org_openpsa_directmarketing_handler_import', 'csv'),
        );

        // Match /campaign/import/vcards/<guid>
        $this->_request_switch[] = array(
            'fixed_args' => Array('campaign', 'import', 'vcards'),
            'variable_args' => 1,
            'handler' => Array('org_openpsa_directmarketing_handler_import', 'vcards'),
        );

        // Match /campaign/export/csv/<guid>/<filename>
        $this->_request_switch[] = array(
            'fixed_args' => Array('campaign', 'export', 'csv'),
            'variable_args' => 2,
            'handler' => Array('org_openpsa_directmarketing_handler_export', 'csv'),
        );
        // Match /campaign/export/csv/<guid>/
        $this->_request_switch[] = array(
            'fixed_args' => Array('campaign', 'export', 'csv'),
            'variable_args' => 1,
            'handler' => Array('org_openpsa_directmarketing_handler_export', 'csv'),
        );

        // Handle /message/create/<campaign guid>/<schema>
        $this->_request_switch['create_message'] = array
        (
            'handler' => array('org_openpsa_directmarketing_handler_message_create', 'create'),
            'fixed_args' => array('message', 'create'),
            'variable_args' => 2,
        );

        // Match /message/new/<campaign guid>/<schema>
        $this->_request_switch['create_message_old'] = array(
            'fixed_args' => Array('message','new'),
            'variable_args' => 2,
            'handler' => array('org_openpsa_directmarketing_handler_message_create', 'create'),
        );

        // Match /message/list/<type>/<guid>
        $this->_request_switch['message_list_dynamic_type'] = array(
            'fixed_args' => Array('message','list'),
            'variable_args' => 2,
            'handler' => array('org_openpsa_directmarketing_handler_message_list', 'list'),
        );

        // Match /message/compose/<message_guid>/<person_guid>
        $this->_request_switch['compose4person'] = array(
            'fixed_args' => array('message', 'compose'),
            'variable_args' => 2,
            'handler' => array('org_openpsa_directmarketing_handler_message_compose', 'compose'),
        );

        // Match /message/compose/<message_guid>
        $this->_request_switch['compose'] = array(
            'fixed_args' => array('message', 'compose'),
            'variable_args' => 1,
            'handler' => array('org_openpsa_directmarketing_handler_message_compose', 'compose'),
        );

        // Match /message/send_bg/<message GUID>/<batch number>/<job guid>
        $this->_request_switch['background_send_message'] = array(
            'fixed_args' => array('message', 'send_bg'),
            'variable_args' => 3,
            'handler' => array('org_openpsa_directmarketing_handler_message_send', 'send_bg'),
        );

        // Match /message/send_delayed/<message GUID>/<time>/
        $this->_request_switch['delayed_send_message'] = array(
            'fixed_args' => array('message', 'send_test'),
            'variable_args' => 2,
            'handler' => array('org_openpsa_directmarketing_handler_message_send', 'send'),
        );

        // Match /message/send/<message GUID>
        $this->_request_switch['send_message'] = array(
            'fixed_args' => array('message', 'send'),
            'variable_args' => 1,
            'handler' => array('org_openpsa_directmarketing_handler_message_send', 'send'),
        );

        // Match /message/send_test/<message GUID>
        $this->_request_switch['test_send_message'] = array(
            'fixed_args' => array('message', 'send_test'),
            'variable_args' => 1,
            'handler' => array('org_openpsa_directmarketing_handler_message_send', 'send'),
        );

        // Match /message/send_status/<message GUID>
        $this->_request_switch['message_send_status'] = array(
            'fixed_args' => array('message', 'send_status'),
            'variable_args' => 1,
            'handler' => array('org_openpsa_directmarketing_handler_message_report', 'status'),
        );

        // Match /message/report/<message GUID>
        $this->_request_switch['message_report'] = array(
            'fixed_args' => array('message', 'report'),
            'variable_args' => 1,
            'handler' => array('org_openpsa_directmarketing_handler_message_report', 'report'),
        );

        // Match /message/<message GUID>
        $this->_request_switch['message_view'] = array(
            'fixed_args' => 'message',
            'variable_args' => 1,
            'handler' => array('org_openpsa_directmarketing_handler_message_message', 'view'),
        );

        // Match /message/edit/<message GUID>
        $this->_request_switch['message_edit'] = array(
            'fixed_args' => array('message', 'edit'),
            'variable_args' => 1,
            'handler' => array('org_openpsa_directmarketing_handler_message_admin', 'edit'),
        );

        // Match /message/delete/<message GUID>
        $this->_request_switch['message_delete'] = array(
            'fixed_args' => array('message', 'delete'),
            'variable_args' => 1,
            'handler' => array('org_openpsa_directmarketing_handler_message_admin', 'delete'),
        );

        // Handle /campaign/create/<schema>
        $this->_request_switch['create_campaign'] = array
        (
            'handler' => array('org_openpsa_directmarketing_handler_campaign_create', 'create'),
            'fixed_args' => array('campaign', 'create'),
            'variable_args' => 1,
        );

        // Match /campaign/list
        $this->_request_switch['list_campaign'] = array(
            'fixed_args' => Array('campaign','list'),
            'handler' => array('org_openpsa_directmarketing_handler_subscriber', 'list'),
        );

        // Match /campaign/list/<person GUID>
        $this->_request_switch['list_campaign_person'] = array(
            'fixed_args' => Array('campaign','list'),
            'handler' => array('org_openpsa_directmarketing_handler_subscriber', 'list'),
            'variable_args' => 1,
        );

        // Match /campaign/unsubscribe/<member GUID>
        $this->_request_switch['subscriber_unsubscribe'] = array(
            'fixed_args' => Array('campaign','unsubscribe'),
            'handler' => array('org_openpsa_directmarketing_handler_subscriber', 'unsubscribe'),
            'variable_args' => 1,
        );

        // Match /campaign/unsubscribe/ajax/<membership GUID>
        $this->_request_switch['subscriber_unsubscribe'] = array(
            'fixed_args' => Array('campaign','unsubscribe', 'ajax'),
            'handler' => array('org_openpsa_directmarketing_handler_subscriber', 'unsubscribe_ajax'),
            'variable_args' => 1,
        );

        // Match /campaign/unsubscribe_all/<person GUID>
        $this->_request_switch['subscriber_unsubscribe_all'] = array(
            'fixed_args' => Array('campaign','unsubscribe_all'),
            'handler' => array('org_openpsa_directmarketing_handler_subscriber', 'unsubscribe_all'),
            'variable_args' => 1,
        );

        // Match /campaign/edit_query/<campaign GUID>
        $this->_request_switch['edit_campaign_query'] = array
        (
            'handler' => array('org_openpsa_directmarketing_handler_campaign_admin', 'edit_query'),
            'fixed_args' => array('campaign', 'edit_query'),
            'variable_args' => 1,
        );

        // Match /campaign/edit_query/<campaign GUID>
        $this->_request_switch['edit_campaign_query_advanced'] = array
        (
            'handler' => array('org_openpsa_directmarketing_handler_campaign_admin', 'edit_query_advanced'),
            'fixed_args' => array('campaign', 'edit_query_advanced'),
            'variable_args' => 1,
        );

        // Match /campaign/edit/<campaign GUID>
        $this->_request_switch['edit_campaign'] = array
        (
            'handler' => array('org_openpsa_directmarketing_handler_campaign_admin', 'edit'),
            'fixed_args' => array('campaign', 'edit'),
            'variable_args' => 1,
        );

        // Match /campaign/delete/<campaign GUID>
        $this->_request_switch['delete_campaign'] = array
        (
            'handler' => array('org_openpsa_directmarketing_handler_campaign_admin', 'delete'),
            'fixed_args' => array('campaign', 'delete'),
            'variable_args' => 1,
        );

        // Match /campaign/<campaign GUID>
        $this->_request_switch['view_campaign'] = array
        (
            'handler' => array('org_openpsa_directmarketing_handler_campaign_campaign', 'view'),
            'fixed_args' => array('campaign'),
            'variable_args' => 1,
        );

        // Match /logger/bounce
        $this->_request_switch['log_bounce'] = array(
            'fixed_args' => array ('logger', 'bounce'),
            'handler' => array('org_openpsa_directmarketing_handler_logger', 'bounce'),
        );

        // Match /logger/link
        $this->_request_switch['log_link'] = array(
            'fixed_args' => array ('logger', 'link'),
            'handler' => array('org_openpsa_directmarketing_handler_logger', 'link'),
        );

        // Match /logger/redirect/<TOKEN>/<URL>
        $this->_request_switch['log_redirect_byurl'] = array(
            'fixed_args' => array ('logger', 'redirect'),
            'variable_args' => 2,
            'handler' => array('org_openpsa_directmarketing_handler_logger', 'redirect'),
        );

        // Match /logger/redirect/<TOKEN>
        $this->_request_switch['log_redirect'] = array(
            'fixed_args' => array ('logger', 'redirect'),
            'variable_args' => 1,
            'handler' => array('org_openpsa_directmarketing_handler_logger', 'redirect'),
        );

        $this->_request_switch['config'] = Array
        (
            'handler' => Array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/org/openpsa/directmarketing/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );

        // Match /debug
        $this->_request_switch['debugger'] = array(
            'fixed_args' => 'debug',
            'handler' => 'debug',
        );

        // Match /
        $this->_request_switch['frontpage'] = array(
            'handler' => 'frontpage'
        );

        // This component uses Ajax, include the handler javascripts
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . "/org.openpsa.helpers/ajaxutils.js");
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . "/org.openpsa.helpers/messages.js");
        // This is no longer autoloaded by core
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/org.openpsa.core/ui-elements.css",
            )
        );
    }

    function _initialize_datamanager($type, $schemadb_snippet)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        // Load schema database snippet or file
        debug_add("Loading Schema Database", MIDCOM_LOG_DEBUG);
        $schemadb_contents = midcom_get_snippet_content($schemadb_snippet);
        eval("\$schemadb = Array ( {$schemadb_contents} );");
        // Initialize the datamanager with the schema
        $this->_datamanagers[$type] = new midcom_helper_datamanager($schemadb);

        if (!$this->_datamanagers[$type]) {
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Datamanager could not be instantiated.");
            // This will exit.
        }
        debug_pop();
    }

    /**
     * Populates the node toolbar depending on the user's rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {
        if ($_MIDCOM->auth->can_user_do('midgard:create', null, 'org_openpsa_directmarketing_campaign'))
        {
            foreach (array_keys($this->_request_data['schemadb_campaign']) as $name)
            {
                $this->_view_toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "campaign/create/{$name}.html",
                        MIDCOM_TOOLBAR_LABEL => sprintf
                        (
                            $this->_l10n_midcom->get('create %s'),
                            $this->_l10n->get($this->_request_data['schemadb_campaign'][$name]->description)
                        ),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_people.png',
                        MIDCOM_TOOLBAR_ACCESSKEY => 'n',
                    )
                );
            }
        }
        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'config.html',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                )
            );
        }
    }

    /**
     * The handle callback populates the toolbars.
     */
    function _on_handle($handler, $args)
    {
        $this->_request_data['schemadb_campaign'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_campaign'));
        $this->_request_data['schemadb_message'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_message'));

        $this->_populate_node_toolbar();

        return true;
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_frontpage($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        /*$this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => 'campaign/new/',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("create campaign"),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_people.png',
                MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_user_do('midgard:create', null, 'org_openpsa_directmarketing_campaign'),
                MIDCOM_TOOLBAR_ACCESSKEY => 'n',
            )
        );*/
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_frontpage($handler_id, &$data)
    {
        midcom_show_style("show-frontpage");
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_debug($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $this->_request_data['config'] =& $this->_config;
        $this->_request_data['datamanagers'] =& $this->_datamanagers;
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_debug($handler_id, &$data)
    {
        midcom_show_style("show-debug");
    }


    function get_messagetype_icon($type)
    {
        $icon = 'stock_mail.png';
        switch ($type)
        {
            case ORG_OPENPSA_MESSAGETYPE_SMS:
            case ORG_OPENPSA_MESSAGETYPE_MMS:
                $icon = 'stock_cell-phone.png';
                break;
            case ORG_OPENPSA_MESSAGETYPE_CALL:
            case ORG_OPENPSA_MESSAGETYPE_FAX:
                $icon = 'stock_landline-phone.png';
                break;
            case ORG_OPENPSA_MESSAGETYPE_SNAILMAIL:
                $icon = 'stock_home.png';
                break;
        }
        return $icon;
    }

    function get_messagetype_css_class($type)
    {
        $class = 'email';
        switch ($type)
        {
            case ORG_OPENPSA_MESSAGETYPE_SMS:
            case ORG_OPENPSA_MESSAGETYPE_MMS:
                $class = 'mobile';
                break;
            case ORG_OPENPSA_MESSAGETYPE_CALL:
            case ORG_OPENPSA_MESSAGETYPE_FAX:
                $class = 'telephone';
                break;
            case ORG_OPENPSA_MESSAGETYPE_SNAILMAIL:
                $class = 'postal';
                break;
        }
        return $class;
    }

}
?>