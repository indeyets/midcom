<?php
/**
 * @package net.nemein.approvenotifier 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
 
/**
 * @package net.nemein.approvenotifier 
 */
class net_nemein_approvenotifier_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the notifications library.
     */
    function __construct()
    {
        parent::__construct();
        $this->_component = 'net.nemein.approvenotifier';
        $this->_purecode = true;
        $this->_autoload_files = Array
        (
            'main.php',
        );
        $this->_autoload_libraries = Array
        (
            'org.openpsa.notifications'
        );
    }

}
?>