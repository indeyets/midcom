<?php
/**
 * @package midcom.admin.simplecontent
 */

class midcom_admin_simplecontent_interface extends midcom_baseclasses_components_interface {

    function midcom_admin_simplecontent_interface() {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'midcom.admin.simplecontent';
        $this->_purecode = true;
        $this->_autoload_files = Array(
            'aegir_handler.php',
            'aegir_navigation.php',
            'article.php',
            'admin.php',
            'topic.php',
            'navigation.php'
            );
        $this->_autoload_libraries = Array(
            'midcom.helper.datamanager',
            'no.bergfald.rcs',
            );

    }

}

?>
