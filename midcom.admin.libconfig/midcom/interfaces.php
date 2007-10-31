<?php
/**
 * Library configuration Interface Class. This is a pure code library.
 *
 * @package midcom.admin.libconfig
 */
class midcom_admin_libconfig_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing needs to be done, besides connecting to the parent class constructor.
     */
    function midcom_admin_libconfig_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'midcom.helper.libconfig';
        $this->_purecode = true;
    }
}
?>