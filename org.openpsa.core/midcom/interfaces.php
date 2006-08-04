<?php
/**
 * OpenPSA core stuff
 * 
 * @package org.openpsa.core
 */
class org_openpsa_core_interface extends midcom_baseclasses_components_interface
{
    
    function org_openpsa_core_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'org.openpsa.core';
        $this->_purecode = true;
        $this->_autoload_files = Array(
            'constants.php',
            'version.php',
            'acl_synchronizer.php',
        );
        $this->_autoload_libraries = Array( 
            'org.openpsa.helpers',
        );
    }
    
    function _on_initialize()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        // Make sure UI message stack is present and initialized
        org_openpsa_helpers_uimessages::initialize_stack();
                
        // Make the ACL selection array available to all components
        if (!array_key_exists('org_openpsa_core_acl_options', $GLOBALS))
        {
            $GLOBALS['org_openpsa_core_acl_options'] = array(
                ORG_OPENPSA_ACCESSTYPE_WGRESTRICTED => 'workgroup restricted',            
                ORG_OPENPSA_ACCESSTYPE_WGPRIVATE => 'workgroup private',
                ORG_OPENPSA_ACCESSTYPE_PRIVATE => 'private',
                ORG_OPENPSA_ACCESSTYPE_PUBLIC => 'public',
                ORG_OPENPSA_ACCESSTYPE_AGGREGATED => 'aggregated',
            );
        }    

        // Make the selected workgroup filter available to all components
        if (!array_key_exists('org_openpsa_core_workgroup_filter', $GLOBALS))
        {
            $session = new midcom_service_session('org.openpsa.core');
            
            if ($this->_data['config']->get('default_workgroup_filter') == 'me')
            {
                if ($_MIDCOM->auth->user)
                {
                
                    $default_filter = $_MIDCOM->auth->user->id;
                }
                else
                {
                    $default_filter = 'all';
                }
            }
            else
            {
                $default_filter = $this->_data['config']->get('default_workgroup_filter');
            }
            
            $GLOBALS['org_openpsa_core_workgroup_filter'] = $default_filter;
            if (!$session->exists('org_openpsa_core_workgroup_filter'))
            {
                $session->set('org_openpsa_core_workgroup_filter', $default_filter);            
            }
            $GLOBALS['org_openpsa_core_workgroup_filter'] = $session->get('org_openpsa_core_workgroup_filter');
        }
        
        // Ensure the UI looks somewhat reasonable regardless of used style
        $_MIDCOM->add_link_head(array(
            'rel' => 'stylesheet',
            'type' => 'text/css',
            'href' => MIDCOM_STATIC_URL."/org.openpsa.core/ui-elements.css",
        ));          
        
        debug_pop();
        return true;
    }
}
?>