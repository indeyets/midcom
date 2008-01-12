<?php
/**
 * @package net.nemein.shoppingcart
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a URL handler class for net.nemein.shoppingcart
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 *
 * @see midcom_baseclasses_components_handler
 * @package net.nemein.shoppingcart
 */
class net_nemein_shoppingcart_handler_checkout_redirect  extends midcom_baseclasses_components_handler
{

    /**
     * Simple default constructor.
     */
    function net_nemein_shoppingcart_handler_checkout_redirect()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * _on_initialize is called by midcom on creation of the handler.
     */
    function _on_initialize()
    {
    }

    /**
     * Handler to redirect to correct backend
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data reference to request_data
     * @return boolean Indicating success.
     */
    function _handler_redirect($handler_id, $args, &$data)
    {
        $handler = $this->_config->get('checkout_backend');
        $_MIDCOM->relocate("checkout/{$handler}.html");
        // This should exit
        return false;
    }

    /**
     * This function does the output.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_redirect($handler_id, &$data)
    {
        // We should not reach this.
    }
}
?>
