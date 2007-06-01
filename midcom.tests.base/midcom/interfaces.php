<?php
/**
 * @package midcom.tests
 */

class midcom_tests_interface extends midcom_baseclasses_components_interface {

    function midcom_tests_interface() {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'midcom.tests';
        $this->_purecode = true;
        $this->_autoload_files = Array(
            'aegir_handler.php',
            'aegir_navigation.php',
            'tests.php',
            
           //'navigation.php',            
            );
    }

}

?>
