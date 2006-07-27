<?php
/**
 * @package midcom.admin.core
 */
class midcom_admin_core_interface extends midcom_baseclasses_components_interface {
    function midcom_admin_core_interface () {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'midcom.admin.core';
        $this->_purecode = true;
        $this->_autoload_files = Array(
            'core.php',
            );
        $this->_autoload_libraries = Array(
            );

    }

}

?>
