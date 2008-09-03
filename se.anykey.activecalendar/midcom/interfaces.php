<?php
/**
 * @package se.anykey.activecalendar
 */

/**
 * Active Calendar is PHP Class, that generates calendars (month or year view) as a HTML Table (XHTML-Valid).
 * http://www.micronetwork.de/activecalendar/
 * Available under LGPL license
 *
 * Startup loads main class, which is used for all operations.
 *
 * @package se.anykey.activecalendar
 */
class se_anykey_activecalendar_interface extends midcom_baseclasses_components_interface
{

    function __construct()
    {
        parent::__construct();

        $this->_component = 'se.anykey.activecalendar';
        $this->_purecode = true;
        $this->_autoload_files = Array('activecalendar.php');
    }

    function _on_initialize()
    {
        return true;
    }
}
?>