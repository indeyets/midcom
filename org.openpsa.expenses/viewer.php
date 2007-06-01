<?php
/**
 * @package org.openpsa.expenses
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which URLs should be handled by this module. 
 * 
 * @package org.openpsa.expenses
 */
class org_openpsa_expenses_viewer extends midcom_baseclasses_components_request
{
    function org_openpsa_expenses_viewer($topic, $config)
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

        // Handle /hours/task/<guid>
        $this->_request_switch['list_hours_task'] = array
        (
            'handler' => array('org_openpsa_expenses_handler_hours_list', 'list'),
            'fixed_args' => array('hours', 'task'),
            'variable_args' => 1,
        );
        
        // Handle /hours/task/all/<guid>
        $this->_request_switch['list_hours_task_all'] = array
        (
            'handler' => array('org_openpsa_expenses_handler_hours_list', 'list'),
            'fixed_args' => array('hours', 'task', 'all'),
            'variable_args' => 1,
        );

        // Handle /hours/between/<from>/<to>
        $this->_request_switch['list_hours_between'] = array
        (
            'handler' => array('org_openpsa_expenses_handler_hours_list', 'list'),
            'fixed_args' => array('hours', 'between'),
            'variable_args' => 2,
        );
        
        // Handle /hours/between/all/<from>/<to>
        $this->_request_switch['list_hours_between_all'] = array
        (
            'handler' => array('org_openpsa_expenses_handler_hours_list', 'list'),
            'fixed_args' => array('hours', 'between', 'all'),
            'variable_args' => 2,
        );

        // Handle /hours/edit/<guid>
        $this->_request_switch['hours_edit'] = array
        (
            'handler' => array('org_openpsa_expenses_handler_hours_admin', 'edit'),
            'fixed_args' => array('hours', 'edit'),
            'variable_args' => 1,
        );
         
        // Handle /hours/create/<schema>
        $this->_request_switch['hours_create'] = array
        (
            'handler' => array('org_openpsa_expenses_handler_hours_admin', 'create'),
            'fixed_args' => array('hours', 'create'),
            'variable_args' => 1,
        );
        
        // Handle /hours/create/<schema>/<task>
        $this->_request_switch['hours_create_task'] = array
        (
            'handler' => array('org_openpsa_expenses_handler_hours_admin', 'create'),
            'fixed_args' => array('hours', 'create'),
            'variable_args' => 2,
        );

        // Handle /
        $this->_request_switch['index'] = array
        (
            'handler' => Array('org_openpsa_expenses_handler_index', 'index'),
        );
    }

    /**
     * Populates the node toolbar depending on the users rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {   
        foreach (array_keys($this->_request_data['schemadb_hours']) as $name)
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "hours/create/{$name}/",
                    MIDCOM_TOOLBAR_LABEL => sprintf
                    (
                        $this->_l10n_midcom->get('create %s'),
                        $this->_l10n->get($this->_request_data['schemadb_hours'][$name]->description)
                    ),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/scheduled_and_shown.png',
                )
            );
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
        $this->_request_data['schemadb_hours'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_hours'));

        $this->_populate_node_toolbar();

        return true;
    }

}

?>
