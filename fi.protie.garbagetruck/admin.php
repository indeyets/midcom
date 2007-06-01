<?php
class fi_protie_garbagetruck_admin extends midcom_baseclasses_components_request_admin
{
    /**
     * Simple constructor. Calls for the baseclass
     * 
     * @access protected
     */
    function fi_protie_garbagetruck_admin($topic, $config)
    {
        parent::midcom_baseclasses_components_request_admin($topic, $config);
    }
    
    /**
     * On initialize scripts
     * 
     * @access private
     */
    function _on_initialize()
    {
        // Configuration
        $this->_request_switch['config'] = Array
        (
            'handler' => 'config_dm',
            // 'fixed_args' => Array('config'),
            'schemadb' => 'file:/fi/protie/garbagetruck/config/schemadb_config.inc',
            'schema' => 'config',
            'disable_return_to_topic' => false
        );
    }
}
?>