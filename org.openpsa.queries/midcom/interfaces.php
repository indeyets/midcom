<?php
/**
 * OpenPSA queries library, handles encoding/sending and decoding.
 *
 * Startup loads main class, which is used for all operations.
 *
 * @package org.openpsa.queries
 */
class org_openpsa_queries_interface extends midcom_baseclasses_components_interface
{

    function org_openpsa_queries_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'org.openpsa.queries';
        $this->_purecode = true;
        $this->_autoload_class_definitions = array('midcom_dba_classes.inc');
        $this->_autoload_files = Array('query.php');
    }
}

?>