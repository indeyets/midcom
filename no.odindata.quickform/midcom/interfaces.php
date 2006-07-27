<?php

/**
 * 
 * 
 * @package no.bergfald.versioning
 * @author Tarjei Huse (tarjei - at -bergfald.no)
 */
class no_odindata_quickform_interface extends midcom_baseclasses_components_interface
{
    
    function no_odindata_quickform_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'no.odindata.quickform';
        $this->_purecode = false;
        $this->_autoload_files = Array(
            'view.php', 'navigation.php', 'admin.php', 'formvar.php'
        );
        $this->_autoload_libraries = Array('midcom.helper.datamanager');
    }
    
    
}

?>