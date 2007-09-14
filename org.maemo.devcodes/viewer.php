<?php
/**
 * @package org.maemo.devcodes
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which URLs should be handled by this module. 
 * 
 * @package org.maemo.devcodes
 */
class org_maemo_devcodes_viewer extends midcom_baseclasses_components_request
{
    function org_maemo_devcodes_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
    }

    /**
     * Initialize the request switch and the content topic.
     *
     * @access protected
     */
    function _on_initialize()
    {
        /**
         * Prepare the request switch, which contains URL handlers for the component
         */
         
        // Handle /config
        $this->_request_switch['config'] = array
        (
            'fixed_args' => Array('config'),
            'handler' => Array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/org/maemo/devcodes/config/schemadb_config.inc',
            'schema' => 'config',
        );

        /**
         * Device request switches
         */
        // Handle /device/create
        $this->_request_switch['create-device'] = array
        (
            'fixed_args' => Array('device', 'create'),
            'handler' => Array('org_maemo_devcodes_handler_device_create', 'create'),
        );

        // Handle /device/edit/<guid>
        $this->_request_switch['edit-device'] = array
        (
            'fixed_args' => Array('device', 'edit'),
            'variable_args' => 1,
            'handler' => Array('org_maemo_devcodes_handler_device_admin', 'edit'),
        );

        // Handle /device/delete/<guid>
        $this->_request_switch['delete-device'] = array
        (
            'fixed_args' => Array('device', 'delete'),
            'variable_args' => 1,
            'handler' => Array('org_maemo_devcodes_handler_device_admin', 'delete'),
        );

        // Handle /device/applyfor/<guid>.html
        $this->_request_switch['applyfor-device'] = array
        (
            'fixed_args' => Array('device', 'applyfor'),
            'variable_args' => 1,
            'handler' => Array('org_maemo_devcodes_handler_application_create', 'apply'),
        );


        // Handle /device/<guid>.html
        $this->_request_switch['view-device'] = array
        (
            'fixed_args' => Array('device'),
            'variable_args' => 1,
            'handler' => Array('org_maemo_devcodes_handler_device_view', 'view'),
        );

        /**
         * Code request switches
         */
        // Handle /code/create
        $this->_request_switch['create-code'] = array
        (
            'fixed_args' => Array('code', 'create'),
            'handler' => Array('org_maemo_devcodes_handler_code_create', 'create'),
        );

        // Handle /code/edit/<guid>
        $this->_request_switch['edit-code'] = array
        (
            'fixed_args' => Array('code', 'edit'),
            'variable_args' => 1,
            'handler' => Array('org_maemo_devcodes_handler_code_admin', 'edit'),
        );

        // Handle /code/delete/<guid>
        $this->_request_switch['delete-code'] = array
        (
            'fixed_args' => Array('code', 'delete'),
            'variable_args' => 1,
            'handler' => Array('org_maemo_devcodes_handler_code_admin', 'delete'),
        );

        // Handle /code/list/<device_guid>
        $this->_request_switch['list-codes'] = array
        (
            'fixed_args' => Array('code', 'list'),
            'variable_args' => 1,
            'handler' => Array('org_maemo_devcodes_handler_code_list', 'list'),
        );

        // Handle /code/assign/process
        $this->_request_switch['assign-codes-process'] = array
        (
            'fixed_args' => Array('code', 'assign', 'process'),
            'handler' => Array('org_maemo_devcodes_handler_code_assign', 'process'),
        );

        // Handle /code/assign/<device_guid>/<area_name>
        $this->_request_switch['assign-codes'] = array
        (
            'fixed_args' => Array('code', 'assign'),
            'variable_args' => 2,
            'handler' => Array('org_maemo_devcodes_handler_code_assign', 'list'),
        );

        // Handle /code/assign/<device_guid>
        $this->_request_switch['assign-codes-countryselector'] = array
        (
            'fixed_args' => Array('code', 'assign'),
            'variable_args' => 1,
            'handler' => Array('org_maemo_devcodes_handler_code_assign', 'select_area'),
        );


        // Handle /code/import/process
        $this->_request_switch['import-codes-process'] = array
        (
            'fixed_args' => Array('code', 'import', 'process'),
            'handler' => Array('org_maemo_devcodes_handler_code_import', 'process'),
        );

        // Handle /code/import/<device_guid>
        $this->_request_switch['import-codes'] = array
        (
            'fixed_args' => Array('code', 'import'),
            'variable_args' => 1,
            'handler' => Array('org_maemo_devcodes_handler_code_import', 'import'),
        );

        // Handle /code/<guid>.html
        $this->_request_switch['view-code'] = array
        (
            'fixed_args' => Array('code'),
            'variable_args' => 1,
            'handler' => Array('org_maemo_devcodes_handler_code_view', 'view'),
        );


        /**
         * Application request switches
         */
        // Handle /application/create
        $this->_request_switch['create-application'] = array
        (
            'fixed_args' => Array('application', 'create'),
            'handler' => Array('org_maemo_devcodes_handler_application_create', 'create'),
        );

        // Handle /application/edit/<guid>
        $this->_request_switch['edit-application'] = array
        (
            'fixed_args' => Array('application', 'edit'),
            'variable_args' => 1,
            'handler' => Array('org_maemo_devcodes_handler_application_admin', 'edit'),
        );

        // Handle /application/delete/<guid>
        $this->_request_switch['delete-application'] = array
        (
            'fixed_args' => Array('application', 'delete'),
            'variable_args' => 1,
            'handler' => Array('org_maemo_devcodes_handler_application_admin', 'delete'),
        );

        // Handle /application/list/my
        $this->_request_switch['list-applications-my'] = array
        (
            'fixed_args' => Array('application', 'list', 'my'),
            'handler' => Array('org_maemo_devcodes_handler_application_list', 'my'),
        );

        // Handle /application/list/<device_guid>
        $this->_request_switch['list-applications'] = array
        (
            'fixed_args' => Array('application', 'list'),
            'variable_args' => 1,
            'handler' => Array('org_maemo_devcodes_handler_application_list', 'list'),
        );

        // Handle /application/<guid>.html
        $this->_request_switch['view-application'] = array
        (
            'fixed_args' => Array('application'),
            'variable_args' => 1,
            'handler' => Array('org_maemo_devcodes_handler_application_view', 'view'),
        );


        // Handle /
        $this->_request_switch['index'] = array
        (
            'handler' => Array('org_maemo_devcodes_handler_index', 'index'),
        );
    }

    /**
     * Populates the node toolbar depending on the users rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {   
        foreach (array_keys($this->_request_data['schemadb']) as $name)
        {
            $this->_node_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "{$name}/create.html",
                MIDCOM_TOOLBAR_LABEL => sprintf
                (
                    $this->_l10n_midcom->get('create %s'),
                    $this->_request_data['schemadb'][$name]->description
                ),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_topic->can_do('midgard:create'),
            ));
        }
        $this->_node_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => 'config.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                MIDCOM_TOOLBAR_ENABLED => ($this->_topic->can_do('midgard:update') && $this->_topic->can_do('midcom:component_config')),
            )
        );
        
    }

    /**
     * The handle callback populates the toolbars.
     */
    function _on_handle($handler, $args)
    {
        $this->_request_data['schemadb'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));

        $this->_populate_node_toolbar();

        return true;
    }

}

?>
