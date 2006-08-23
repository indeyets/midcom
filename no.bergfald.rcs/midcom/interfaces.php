<?php
/**
 * 
 * 
 * @package no.bergfald.versioning
 * @author Tarjei Huse (tarjei - at -bergfald.no)
 */
class no_bergfald_rcs_interface extends midcom_baseclasses_components_interface
{
    
    function no_bergfald_rcs_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'no.bergfald.rcs';
        $this->_purecode = true;
        $this->_autoload_files = array
        (
            'rcs.php',
            'handler.php',
            'backends/aegirrcs.php',
            'aegir_handler.php'
        );
        $this->_autoload_libraries = array(
            'midcom.helper.xml',
            'org.openpsa.contactwidget'
        );
    }
    
    
}
?>