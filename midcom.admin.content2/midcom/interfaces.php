<?php
/**
 * @package midcom.admin.content2
 */
class midcom_admin_content2_interface extends midcom_baseclasses_components_interface {
    function midcom_admin_content2_interface () {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'midcom.admin.content2';
        $this->_purecode = true;
        $this->_autoload_files = Array(
            'aegir_handler.php',
            'aegir_navigation.php',
            'config.php',
            //'admincontext.php',
            'callbacks/styleselector.php',
            //'context.php',
            
            'view.php',
            //'admin.php',
            'navigation.php'
            );
        $this->_autoload_libraries = Array(
            'midcom.helper.datamanager2',
            'no.bergfald.rcs',
            );

    }

}

?>
