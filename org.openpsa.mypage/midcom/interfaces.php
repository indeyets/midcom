<?php
/**
 * OpenPSA Personal Summary component
 * 
 * @package org.openpsa.mypage
 */
class org_openpsa_mypage_interface extends midcom_baseclasses_components_interface
{
    
    function org_openpsa_mypage_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'org.openpsa.mypage';
        $this->_autoload_files = array(
            'viewer.php',
            'admin.php',
            'navigation.php'
        );
        $this->_autoload_libraries = array( 
            'org.openpsa.core', 
            'org.openpsa.helpers',
            'midcom.helper.datamanager',
            'org.openpsa.contactwidget',
        );
        
    }    
    
}
?>