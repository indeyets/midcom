<?php

/* Temporary topic management replacement, refactored out of _cmdtopic.
 * no comments etc. because this is already superseeded by AIS2 and just
 * a proof-of-concept implementation
 */

class midcom_admin_core_plugin 
{
    var $_anchor_prefix = null;
    var $_processing_msg = '';
    var $_newtopic = null;

    function midcom_admin_core_plugin()
    {
        
    }

    function _on_initialize()
    {
        $config = $this->_request_data['plugin_config'];
        if ($config)
        {
            foreach ($config as $key => $value)
            {
                $this->$key = $value;
            }
        }
        $this->_anchor_prefix = $this->_request_data['plugin_anchorprefix'];
        
        $this->add_toolbars();
    }

    function get_plugin_handlers()
    {
        return Array
        (
            'create' => Array
            (
                'handler' => Array('midcom_admin_content2_config', 'create'),
                'fixed_args' => 'create',
                'variable_args' => 1,
            ),
            'edit' => Array
            (
                'handler' => Array('midcom_admin_content2_config', 'edit'),
                'fixed_args' => 'edit',
                'variable_args' => 1,
            ),
            'delete' => Array
            (
                'handler' => Array('midcom_admin_content2_config', 'delete'),
                'fixed_args' => 'delete',
                'variable_args' => 1,
            ),
        );
    }
    
    /**
     * Static funtion to get the config. Used by admin plugins. 
     */
    function &get_handler($component ) {
        
        require MIDCOM_ROOT . '/midcom/admin/core/config.php';
        /*
        if (!class_exists($class)) {
            require MIDCOM_ROOT . '/' . str_replace( '.', '/',$component) . '/aegir_handler.php';
        }
        
        $config = & new $class;
        $config->_module = $component;
        return $config;
        */
        $config = & new midcom_admin_core_config($component);
        $config->initialize();
        return $config;
    }  

}

