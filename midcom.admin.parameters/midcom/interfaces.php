<?php
/**
 * @package midcom.admin.parameters
 */
class midcom_admin_parameters_interface extends midcom_baseclasses_components_interface {
    function midcom_admin_parameters_interface () {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'midcom.admin.parameters';
        $this->_purecode = true;
        $this->_autoload_files = Array(
            'parameters.php',
            );
        $this->_autoload_libraries = Array(
            'midcom.helper.datamanager2',
            );

    }

}

?>
