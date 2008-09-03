<?php
/**
 * @package com.magnettechnologies.contactgrabber 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for com.magnettechnologies.contactgrabber
 * 
 * @package com.magnettechnologies.contactgrabber
 */
class com_magnettechnologies_contactgrabber_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function com_magnettechnologies_contactgrabber_interface()
    {
        parent::__construct();
        $this->_component = 'com.magnettechnologies.contactgrabber';
        $this->_purecode = true;

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'contactgrabber.php', 
        );
    }

}
?>