<?php
/**
 * @package midcom.admin.babel
 */
class midcom_admin_babel_interface extends midcom_baseclasses_components_interface
{
    function midcom_admin_babel_interface ()
    {
        parent::__construct();

        $this->_component = 'midcom.admin.settings';
        $this->_purecode = true;
    }

}

?>