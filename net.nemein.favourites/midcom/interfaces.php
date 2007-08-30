<?php

/**
 * Favourites MidCOM interface class.
 * @package net.nemein.favourites
 */
class net_nemein_favourites_interface extends midcom_baseclasses_components_interface
{
   /**
    * Constructor.
    * 
    * Nothing fancy, loads all script files and the datamanager library.
    */
    function net_nemein_favourites_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'net.nemein.favourites';
        $this->_autoload_files = array
        (
            'favourite.php',
            'viewer.php', 
            'navigation.php',
            'admin.php',
        );
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2',
        );
    }
}

?>
