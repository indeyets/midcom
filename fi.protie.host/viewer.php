<?php
class fi_protie_host_viewer extends midcom_baseclasses_components_request
{
    /**
     * Define the request handlers
     * 
     * @access public
     * @param midcom_db_topic $topic
     * @param array $config
     */
    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);
    }
    
    function _on_initialize()
    {
        $this->_request_switch['welcome'] = array
        (
            'handler' => array
            (
                'fi_protie_host_handler_list',
                'list',
            ),
        );
        
        $this->_request_switch['create'] = array
        (
            'handler' => array
            (
                'fi_protie_host_handler_create',
                'create',
            ),
            'fixed_args' => array
            (
                'create',
            ),
        );
        
        $this->_request_switch['edit'] = array
        (
            'handler' => array
            (
                'fi_protie_host_handler_edit',
                'edit',
            ),
            'fixed_args' => array
            (
                'edit',
            ),
            'variable_args' => 1,
        );
        
        $this->_request_switch['delete'] = array
        (
            'handler' => array
            (
                'fi_protie_host_handler_delete',
                'delete',
            ),
            'fixed_args' => array
            (
                'delete',
            ),
            'variable_args' => 1,
        );
    }
    
    /**
     * Load the common elements
     * 
     * @access public
     * @param String $handler_id      Handler ID
     * @param Array  $args            Variable arguments
     * @return boolean
     */
    function _on_handle($handler_id, $args)
    {
        $_MIDCOM->auth->require_admin_user();
        
        // Load the schemadb
        $this->_request_data['schemadb'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
        
        // Add DM2 legacy css
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/legacy.css',
            )
        );
        
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => 'create/',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create a new host'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
            )
        );
        
        if (isset($args[0]))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "delete/{$args[0]}",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                )
            );
        }
        
        return true;
    }
}
?>