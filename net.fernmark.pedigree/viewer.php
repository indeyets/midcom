<?php
/**
 * @package net.fernmark.pedigree
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which URLs should be handled by this module. 
 * 
 * @package net.fernmark.pedigree
 */
class net_fernmark_pedigree_viewer extends midcom_baseclasses_components_request
{
    function net_fernmark_pedigree_viewer($topic, $config)
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
            'handler' => Array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/net/fernmark/pedigree/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );

        // Handle /dog/<guid>.html
        $this->_request_switch['view-dog'] = array
        (
            'fixed_args' => array('dog'),
            'variable_args' => 1,
            'handler' => Array('net_fernmark_pedigree_handler_dog_view', 'view'),
        );
        // Handle /dog/edit/<guid>.html
        $this->_request_switch['edit-dog'] = array
        (
            'fixed_args' => array('dog', 'edit'),
            'variable_args' => 1,
            'handler' => Array('net_fernmark_pedigree_handler_dog_admin', 'edit'),
        );
        // Handle /dog/delete/<guid>.html
        $this->_request_switch['delete-dog'] = array
        (
            'fixed_args' => array('dog', 'delete'),
            'variable_args' => 1,
            'handler' => Array('net_fernmark_pedigree_handler_dog_admin', 'delete'),
        );
        // Handle /create/dog.html
        $this->_request_switch['create-dog'] = array
        (
            'fixed_args' => array('create', 'dog'),
            'handler' => Array('net_fernmark_pedigree_handler_dog_create', 'create'),
        );
        // Handle /create/dog/<guid>.html
        $this->_request_switch['create-dog-wparent'] = array
        (
            'fixed_args' => array('create', 'dog'),
            'variable_args' => 1,
            'handler' => Array('net_fernmark_pedigree_handler_dog_create', 'create'),
        );

        // Handle /result/<guid>.html
        $this->_request_switch['view-result'] = array
        (
            'fixed_args' => array('result'),
            'variable_args' => 1,
            'handler' => Array('net_fernmark_pedigree_handler_result_view', 'view'),
        );
        // Handle /result/edit/<guid>.html
        $this->_request_switch['edit-result'] = array
        (
            'fixed_args' => array('result', 'edit'),
            'variable_args' => 1,
            'handler' => Array('net_fernmark_pedigree_handler_result_admin', 'edit'),
        );
        // Handle /result/delete/<guid>.html
        $this->_request_switch['delete-result'] = array
        (
            'fixed_args' => array('result', 'delete'),
            'variable_args' => 1,
            'handler' => Array('net_fernmark_pedigree_handler_result_admin', 'delete'),
        );
        // Handle /create/result.html
        $this->_request_switch['create-result'] = array
        (
            'fixed_args' => array('create', 'result'),
            'handler' => Array('net_fernmark_pedigree_handler_result_create', 'create'),
        );
        // Handle /create/result/<guid>.html
        $this->_request_switch['create-result-fdog'] = array
        (
            'fixed_args' => array('create', 'result'),
            'variable_args' => 1,
            'handler' => Array('net_fernmark_pedigree_handler_result_create', 'create'),
        );

        // Handle /
        $this->_request_switch['index'] = array
        (
            'handler' => Array('net_fernmark_pedigree_handler_index', 'index'),
        );
    }

    /**
     * Populates the node toolbar depending on the user's rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {   
        $this->_node_toolbar->add_item
        (
            Array
            (
                MIDCOM_TOOLBAR_URL => "create/dog.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('new dog'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_topic->can_do('midgard:create'),
            )
        );
        $this->_node_toolbar->add_item
        (
            Array
            (
                MIDCOM_TOOLBAR_URL => "create/result.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('new result for dog'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_topic->can_do('midgard:create'),
            )
        );
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
        $this->_request_data['schemadb'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
        $this->_populate_node_toolbar();
        return true;
    }

}

?>