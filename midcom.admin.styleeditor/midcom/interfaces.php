<?php
/**
 * @package midcom.admin.styleeditor
 */

class midcom_admin_styleeditor_interface extends midcom_baseclasses_components_interface {

    function midcom_admin_styleeditor_interface() {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'midcom.admin.styleeditor';
        $this->_purecode = true;
        $this->_autoload_files = Array(
            'aegir_handler.php',
            'aegir_navigation.php',
            'admin.php',
            'navigation.php',
            'stylefinder.php' ,
            'toolbarfactory.php',
            
            );
        $this->_autoload_libraries = Array(
            'midcom.helper.datamanager2',
            );

    }

}

?>
