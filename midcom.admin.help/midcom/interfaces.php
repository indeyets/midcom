<?php
/**
 * @package midcom.admin.help
 */
class midcom_admin_help_interface extends midcom_baseclasses_components_interface {
    function midcom_admin_help_interface () {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'midcom.admin.help';
        $this->_purecode = true;
        $this->_autoload_files = Array(
            'help.php',
            );

    }

}

?>
