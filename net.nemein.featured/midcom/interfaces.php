<?php

/**
 * Featured MidCOM interface class.
 * @package net.nemein.favourites
 */
class net_nemein_featured_interface extends midcom_baseclasses_components_interface
{
   /**
    * Constructor.
    * 
    * Nothing fancy, loads all script files and the datamanager library.
    */
    function net_nemein_featured_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'net.nemein.featured';
        $this->_autoload_files = array
        (
	    'featured.php',
	    'viewer.php', 
	    'navigation.php'
	);
	$this->_autoload_libraries = array
	(
            'midcom.helper.datamanager2',
	);
    }	
}

?>
