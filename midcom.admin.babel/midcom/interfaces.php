<?php
/**
 * @package midcom.admin.babel
 */
class midcom_admin_babel_interface extends midcom_baseclasses_components_interface
{
    function __construct()
    {
        parent::__construct();

        $this->_component = 'midcom.admin.settings';
        $this->_purecode = true;
    }

}

?>