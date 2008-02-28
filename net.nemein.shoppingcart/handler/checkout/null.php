<?php
/**
 * @package net.nemein.shoppingcart
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is an URL handler class for net.nemein.shoppingcart
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 * 
 * @package net.nemein.shoppingcart
 */
class net_nemein_shoppingcart_handler_checkout_null  extends midcom_baseclasses_components_handler 
{
    var $_schemadb = false;
    var $_datamanager = false;
    var $_controller = false;
    var $_defaults = array();
    var $_sendto = false;
    var $_mail = false;

    /**
     * Simple default constructor.
     */
    function net_nemein_shoppingcart_handler_checkout_null()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * _on_initialize is called by midcom on creation of the handler. 
     */
    function _on_initialize()
    {
        if (!net_nemein_shoppingcart_viewer::get_items($this->_request_data))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not initialize cart');
            // This will exit
        }
        $data['l10n'] =& $this->_l10n;
    }

    /**
     * Handler for starting simple email checkout
     *
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     * @param array $data reference to request_data
     */
    function _handler_phase1($handler_id, $args, &$data)
    {
        if (empty($data['items']))
        {
            // Cart is empty, can't checkout...
            $_MIDCOM->relocate('contents/');
            // This will exit
        }
        $data['title'] = $this->_l10n->get('view cart');
        $_MIDCOM->set_pagetitle($data['title']);
        $this->_update_breadcrumb_line($handler_id);
        $data['total_value'] = 0;
        $data['permalinks'] = new midcom_services_permalinks();

        return true;
    }


    /**
     * This function does the output.
     *  
     */
    function _show_phase1($handler_id, &$data)
    {
        midcom_show_style('checkout-null');
    }


    /**
     * Helper, updates the context so that we get a complete breadcrum line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line(&$handler_id)
    {
        $data =& $this->_request_data;
        $tmp = Array();

        switch ($handler_id)
        {
            default:
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => 'contents/',
                    MIDCOM_NAV_NAME => $this->_l10n->get('view cart'),
                );
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => 'checkout/null/',
                    MIDCOM_NAV_NAME => $data['title'],
                );
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}
?>