<?php
/**
 * @package midcom.admin.settings
 */
class midcom_admin_babel_interface extends midcom_baseclasses_components_interface 
{
    function midcom_admin_babel_interface () 
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'midcom.admin.settings';
        $this->_purecode = true;
    }

}

?>
