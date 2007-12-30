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
class net_nemein_shoppingcart_handler_cart  extends midcom_baseclasses_components_handler
{

    /**
     * Simple default constructor.
     */
    function net_nemein_shoppingcart_handler_cart()
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
    }

    /**
     * Handler to add an item to the cart
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data reference to request_data
     * @return bool Indicating success.
     */
    function _handler_additem($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $product = new org_openpsa_products_product_dba($args[0]);
        if (   !is_object($product)
            || empty($product->guid))
        {
            debug_add("Given argument '{$args[0]}' does not point to a valid product, ignoring silently",  MIDCOM_LOG_ERROR);
            debug_pop();
            // PONDER: register UImessage (though displaying it might be hard... since we don't reload the parent viewport)
            $_MIDCOM->relocate('shortlist.html');
            // this will exit
        }

        if (!isset($data['items'][$product->guid]))
        {
            debug_add("product '{$product->code}' not yet in cart, initializing");
            $data['items'][$product->guid] = array
            (
                'product_obj' => $product,
                'amount' => 0,
            );
        }
        $item_line =& $data['items'][$product->guid];
        $item_line['amount']++;
        debug_add("product '{$product->code}' amount in cart: {$item_line['amount']}");

        if (!net_nemein_shoppingcart_viewer::save_items($this->_request_data))
        {
            debug_add('FATAL: Could not save items to session', MIDCOM_LOG_ERROR);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not save shopping cart to session data');
            // This will exit
        }

        debug_pop();
        $_MIDCOM->relocate('shortlist.html');
        // this will exit
    }

    /**
     * This function does the output.
     *
     */
    function _show_additem($handler_id, &$data)
    {
        // We should not reach this.
    }


    /**
     * Handler for displaying the cart (i)frame contents
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data reference to request_data
     * @return bool Indicating success.
     */
    function _handler_shortlist($handler_id, $args, &$data)
    {
        $data['l10n'] =& $this->_l10n;
        $_MIDCOM->skip_page_style = true;

        return true;
    }

    /**
     * This function does the output.
     *
     */
    function _show_shortlist($handler_id, &$data)
    {
        if (count($data['items']) == 0)
        {
            midcom_show_style('view-shortlist-empty');
            return;
        }
        midcom_show_style('view-shortlist');
    }

    /**
     * Handler for displaying the cart contents view
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data reference to request_data
     * @return bool Indicating success.
     */
    function _handler_contents($handler_id, $args, &$data)
    {
        $data['l10n'] =& $this->_l10n;
        $data['title'] = $this->_l10n_midcom->get('view cart');
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
    function _show_contents($handler_id, &$data)
    {
        if (count($data['items']) == 0)
        {
            midcom_show_style('view-cart-empty');
            return;
        }
        midcom_show_style('view-cart');
    }

    /**
     * Handler for displaying the cart management view
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data reference to request_data
     * @return bool Indicating success.
     */
    function _handler_manage($handler_id, $args, &$data)
    {
        $this->_handler_manage_handle_post($data);
        $data['l10n'] =& $this->_l10n;
        $data['title'] = $this->_l10n->get('edit cart');
        $_MIDCOM->set_pagetitle($data['title']);
        $this->_update_breadcrumb_line($handler_id);
        $data['total_value'] = 0;
        $data['permalinks'] = new midcom_services_permalinks();

        return true;
    }

	/**
     * @param Array &$data The local request data.
	 */
    function _handler_manage_handle_post(&$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (   !isset($_POST['net_nemein_shoppingcart_managecart_amount'])
            || !is_array($_POST['net_nemein_shoppingcart_managecart_amount']))
        {
            debug_add('net_nemein_shoppingcart_managecart_amount not found in _POST');
            debug_pop();
            return;
        }
        if (   !isset($_POST['net_nemein_shoppingcart_managecart_update'])
            && !isset($_POST['net_nemein_shoppingcart_managecart_checkout']))
        {
            debug_add('Shopping cart data in _POST without update/checkout command, not doing anything', MIDCOM_LOG_WARN);
            debug_pop();
            return;
        }
        foreach ($_POST['net_nemein_shoppingcart_managecart_amount'] as $key => $amount)
        {
            debug_add("setting value for key '{$key}' to '{$amount}'");
            $data['items'][$key]['amount'] = $amount;
        }
        if (!net_nemein_shoppingcart_viewer::save_items($data))
        {
            debug_add('FATAL: Could not save items to session', MIDCOM_LOG_ERROR);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not save shopping cart to session data');
            // This will exit
        }
        if (isset($_POST['net_nemein_shoppingcart_managecart_checkout']))
        {
            // We wish to proceed to checkout immediately after update
            $handler = $this->_config->get('checkout_backend');
            $_MIDCOM->relocate("checkout/{$handler}.html");
            // This will exit
        }
        debug_pop();
    }

    /**
     * This function does the output.
     *
     */
    function _show_manage($handler_id, &$data)
    {
        if (count($data['items']) == 0)
        {
            midcom_show_style('manage-cart-empty');
            return;
        }
        midcom_show_style('manage-cart-header');
        foreach ($data['items'] as $item)
        {
            $data['item'] =& $item;
            midcom_show_style('manage-cart-item');
            unset($data['item']);
        }
        midcom_show_style('manage-cart-totals');
        midcom_show_style('manage-cart-footer');
    }

    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line(&$handler_id)
    {
        $tmp = Array();

        switch ($handler_id)
        {
            case 'cart-manage':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => 'contents.html',
                    MIDCOM_NAV_NAME => $this->_l10n->get('view cart'),
                );
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => '',
                    MIDCOM_NAV_NAME => $this->_l10n->get('edit cart'),
                );
                break;
            case 'cart-view':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => '',
                    MIDCOM_NAV_NAME => $this->_l10n->get('edit cart'),
                );
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => 'contents.html',
                    MIDCOM_NAV_NAME => $this->_l10n->get('view cart'),
                );
                break;
        }
        if (count($tmp) > 0)
        {
            $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        }
    }
}
?>
