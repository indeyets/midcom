<?php
/**
 * @package org.routamc.statusmessage
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which URLs should be handled by this module. 
 * 
 * @package org.routamc.statusmessage
 */
class org_routamc_statusmessage_viewer extends midcom_baseclasses_components_request
{
    function org_routamc_statusmessage_viewer($topic, $config)
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
            'handler' => array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/org/routamc/statusmessage/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => array
            (
                'config'
            ),
        );

        // Handle /microsummary/all
        $this->_request_switch['list_microsummary_all'] = array
        (
            'handler' => array('org_routamc_statusmessage_handler_list', 'microsummary'),
            'fixed_args' => array
            (
                'microsummary',
                'all',
            ),
        );

        // Handle /microsummary/<user>
        $this->_request_switch['list_microsummary'] = array
        (
            'handler' => array('org_routamc_statusmessage_handler_list', 'microsummary'),
            'fixed_args' => array
            (
                'microsummary',
            ),
            'variable_args' => 1,
        );


        // Handle /latest/<user>/<n>
        $this->_request_switch['list_latest'] = array
        (
            'handler' => array('org_routamc_statusmessage_handler_list', 'latest'),
            'fixed_args' => array
            (
                'latest',
            ),
            'variable_args' => 2,
        );


        // Handle /
        $this->_request_switch['list_latest_front'] = array
        (
            'handler' => array('org_routamc_statusmessage_handler_list', 'latest'),
        );
    }

    /**
     * Populates the node toolbar depending on the users rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {
        if ($this->_topic->can_do('midgard:create'))
        {
            foreach (array_keys($this->_request_data['schemadb']) as $name)
            {
                $this->_node_toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "create/{$name}.html",
                        MIDCOM_TOOLBAR_LABEL => sprintf
                        (
                            $this->_l10n_midcom->get('create %s'),
                            $this->_request_data['schemadb'][$name]->description
                        ),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
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
        $this->_request_data['schemadb'] =
            midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));

        $this->_populate_node_toolbar();

        return true;
    }

}

?>
