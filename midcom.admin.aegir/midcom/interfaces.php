<?php
/**
 * Created on Aug 3, 2005
 * @package midcom.admin.aegir
 */
class midcom_admin_aegir_interface extends midcom_baseclasses_components_interface {

    function midcom_admin_aegir_interface() {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'midcom.admin.aegir';
        $this->_autoload_files = Array('view.php','aegir_navigation.php','admin.php', 'aegir_module.php', 'navigation.php');

    }

}


 

?>
