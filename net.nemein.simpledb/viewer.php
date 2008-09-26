<?php
/**
 * @package net.nemein.simpledb
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Simpledb Viewer interface class.
 * 
 * @package net.nemein.simpledb
 */
class net_nemein_simpledb_viewer extends midcom_baseclasses_components_request
{

    function __construct($topic, $config) 
    {
        parent::__construct($topic, $config);
    }
    
    /**
     * Initialize the request switch and the content topic.
     *
     * @access protected
     */
    function _on_initialize()
    {
        // Match /view/<guid>
        $this->_request_switch['view'] = array
        (
            'handler' => array('net_nemein_simpledb_handler_view', 'view'),
            'fixed_args' => array('view'),
            'variable_args' => 1,
        );
        
        // Category ID equals to the schema layout name
        // Match /category/<category id>
        $this->_request_switch['category'] = array
        (
            'handler' => array ('net_nemein_simpledb_handler_search', 'category'),
            'fixed_args' => array ('category'),
            'variable_args' => 1
        );
        
        // Match /create/
        $this->_request_switch['create'] = array
        (
            'handler' => array('net_nemein_simpledb_handler_admin', 'create'),
            'fixed_args' => array('create'),
        );
        
        // Match /edit/<guid>
        $this->_request_switch['edit'] = array
        (
            'handler' => array('net_nemein_simpledb_handler_admin', 'edit'),
            'fixed_args' => array('edit'),
            'variable_args' => 1,
        );
        
        // Match /delete/<guid>
        $this->_request_switch['delete'] = array
        (
            'handler' => array('net_nemein_simpledb_handler_admin', 'delete'),
            'fixed_args' => array('delete'),
            'variable_args' => 1,
        );
    
        // Match /
        $this->_request_switch['search'] = array
        (
            'handler' => array('net_nemein_simpledb_handler_search', 'search'),
        );
        
        // Match /export/<type>
        $this->_request_switch['export_to_type'] = array
        (
            'handler'       => array ('net_nemein_simpledb_handler_export', 'export'),
            'fixed_args'    => array ('export'),
            'variable_args' => 1,
        );
        
        // Match /config/
        $this->_request_switch['config'] = array
        (
            'handler' => array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/net/nemein/simpledb/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => array('config'),
        );
        
        // Match /<key>/<query>/
        $this->_request_switch['quick_search'] = array
        (
            'handler' => array ('net_nemein_simpledb_handler_search', 'quick'),
            'variable_args' => 2,
        );
    }
    
    function _on_handle($handler_id, $args)
    {
        $this->_request_data['datamanager'] = new midcom_helper_datamanager($this->_config->get('schemadb'));
        if (!$this->_request_data['datamanager'])
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to instantiate datamanager for schema database ' . $this->_config->get('schemadb'));
            // This will exit.
        }
        
        $this->_request_data['schema_name'] = $this->_config->get('topic_schema');
        $this->_request_data['schema_fields'] = $this->_request_data['datamanager']->_layoutdb[$this->_request_data['schema_name']]['fields'];

        $this->_node_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => 'create/',
                MIDCOM_TOOLBAR_LABEL => sprintf($this->_request_data['l10n_midcom']->get('create %s'), $this->_l10n->get($this->_request_data['l10n']->get($this->_request_data['datamanager']->_layoutdb[$this->_request_data['schema_name']]['description']))),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-html.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_topic->can_do('midgard:create'),
                MIDCOM_TOOLBAR_ACCESSKEY => 'n',
            )
        );
        
        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'config/',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                )
            );
        }
        
        $this->_node_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => 'export/excel/',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('export database to excel'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/printer.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_topic->can_do('midgard:read'),
            )
        );
        
        $this->_request_data['folder_name'] = $this->_topic->extra;
        
        return parent::_on_handle($handler_id, $args);
    }
    
} 

?>