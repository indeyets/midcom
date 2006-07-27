<?php
/**
 * @package midcom.admin.acls
 */
class midcom_admin_acls_interface extends midcom_baseclasses_components_interface {
    function midcom_admin_acls_interface () {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'midcom.admin.acls';
        $this->_purecode = true;
        $this->_autoload_files = Array(
            'acls.php',
            );
        $this->_autoload_libraries = Array(
            'midcom.helper.datamanager2',
            );

    }

}

?>
