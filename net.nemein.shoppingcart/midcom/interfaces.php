<?php
/**
 * @package net.nemein.shoppingcart 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for net.nemein.shoppingcart
 * 
 * @package net.nemein.shoppingcart
 */
class net_nemein_shoppingcart_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_shoppingcart_interface()
    {
        parent::__construct();
        $this->_component = 'net.nemein.shoppingcart';

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'helpers.php',
            'viewer.php', 
            'navigation.php'
        );
        
        // Load all libraries used by component here
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2'
        );
    }

    function _on_initialize()
    {
        if (!$_MIDCOM->componentloader->load_graceful('org.openpsa.products'))
        {
            return false;
        }
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . "/net.nemein.shoppingcart/cart.css",
            )
        );
        return true;
    }

}
?>