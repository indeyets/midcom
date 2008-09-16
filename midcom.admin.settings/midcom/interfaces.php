<?php
/**
 * @package midcom.admin.settings
 */

/**
 * @package midcom.admin.settings
 */
class midcom_admin_settings_interface extends midcom_baseclasses_components_interface
{
    function __construct()
    {
        parent::__construct();

        $this->_component = 'midcom.admin.settings';
        $this->_purecode = true;
        $this->_autoload_files = Array
        (
            'editor.php',
        );
        $this->_autoload_libraries = Array
        (
            'midcom.helper.datamanager2',
            'midcom.admin.folder',
        );
    }
}
?>