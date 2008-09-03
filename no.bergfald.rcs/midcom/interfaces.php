<?php
/**
 *
 *
 * @package no.bergfald.rcs
 * @author Tarjei Huse (tarjei - at -bergfald.no)
 */

/**
 * @package no.bergfald.rcs
 */
class no_bergfald_rcs_interface extends midcom_baseclasses_components_interface
{

    function no_bergfald_rcs_interface()
    {
        parent::__construct();

        $this->_component = 'no.bergfald.rcs';
        $this->_purecode = true;
        $this->_autoload_files = array
        (
            'rcs.php',
            'handler.php',
            'backends/aegirrcs.php',
            /*'aegir_handler.php'*/
        );
        $this->_autoload_libraries = array(
            'midcom.helper.xml',
            'org.openpsa.contactwidget'
        );
    }


}
?>