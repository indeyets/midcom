<?php
/**
 * Banned words MidCOM interface class.
 * @package net.nemein.bannedwords
 */

/**
 * @package net.nemein.bannedwords
 */
class net_nemein_bannedwords_interface extends midcom_baseclasses_components_interface
{
   /**
    * Constructor.
    *
    * Nothing fancy, loads all script files and the datamanager library.
    */
    function net_nemein_bannedwords_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'net.nemein.bannedwords';
        $this->_autoload_files = array
        (
            'formatters.php',
        );
    }
}

?>