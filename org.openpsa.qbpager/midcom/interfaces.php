<?php
/**
 * OpenPSA qbpager library, handles paging of QB resultsets.
 *
 * Startup loads main class, which is used for all operations.
 *
 * @package org.openpsa.qbpager
 */
class org_openpsa_qbpager_interface extends midcom_baseclasses_components_interface
{

    function org_openpsa_qbpager_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'org.openpsa.qbpager';
        $this->_purecode = true;
        $this->_autoload_files = Array('pager.php', 'pager_direct.php');
    }

    function _on_initialize()
    {
        return true;
    }
}


?>