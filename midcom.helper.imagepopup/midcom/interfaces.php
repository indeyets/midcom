<?php
/**
 * @package midcom.admin.attachments
 */
class midcom_helper_imagepopup_interface extends midcom_baseclasses_components_interface {
    function midcom_helper_imagepopup_interface () {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'midcom.helper.imagepopup';
        $this->_purecode = true;
        $this->_autoload_files = Array(
            //'list.php',
            //'single.php',
            'attachmentlist/simple.php',
            'attachment.php'
            );
        $this->_autoload_libraries = Array(
            'midcom.helper.datamanager2',
            );

    }

}

?>
