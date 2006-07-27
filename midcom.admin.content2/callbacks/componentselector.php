<?php

/**
 * The callback class used to get the midcom manifests. 
 * used by midcom.admin.content2_config.
 * @package midcom.admin.content2
 * */ 
 
class midcom_admin_content2_callbacks_componentselector  {

    var $_manifests = array();
    function midcom_admin_content2_callbacks_componentselector  () {
        //$this->_manifests = array_keys($_MIDCOM->componentloader->manifests);
        foreach ( $_MIDCOM->componentloader->manifests as $key => $manifest ) {
            if (!$manifest->purecode) {
                $this->_manifests[$key] = $key;
            }
        }
        
    }
    
    function set_type(&$type) {}

    function get_name_for_key($key) 
    { 
        return $this->_manifests[$key] ; 
    }

    function key_exists($key) 
    { 
        return array_key_exists($key, $this->_manifests); 
    }
    

    function list_all() { 
        return $this->_manifests; 
    }  
    
}