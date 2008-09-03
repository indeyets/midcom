<?php
/**
 * @package net.nemein.favourites
 */

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
        parent::__construct();

        $this->_component = 'net.nemein.favourites';
    }
}

?>