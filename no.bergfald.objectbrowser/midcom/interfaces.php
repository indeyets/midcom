<?php

/**
 * 
 * 
 * @package no.bergfald.versioning
 * @author Tarjei Huse (tarjei - at -bergfald.no)
 */
class no_bergfald_objectbrowser_interface extends midcom_baseclasses_components_interface
{
    
    function no_bergfald_objectbrowser_interface ()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'no.bergfald.objectbrowser';
        $this->_purecode = true;
        $this->_autoload_files = Array(
            'aegir_handler.php', 
            'aegir_navigation.php',
            'schema.php',
            'object.php' 
        );
        $this->_autoload_libraries = Array('midcom.helper.datamanager');
    }
    
    
}

?>
