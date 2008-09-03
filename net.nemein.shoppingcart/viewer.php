<?php
/**
 * @package net.nemein.shoppingcart
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which URLs should be handled by this module. 
 * 
 * @package net.nemein.shoppingcart
 */
class net_nemein_shoppingcart_viewer extends midcom_baseclasses_components_request
{
    function net_nemein_shoppingcart_viewer($topic, $config)
    {
        parent::__construct($topic, $config);
    }

    /**
     * Initialize the request switch and the content topic.
     *
     * @access protected
     */
    function _on_initialize()
    {
        /**
         * Prepare the request switch, which contains URL handlers for the component
         */
         
        // Handle /config
        $this->_request_switch['config'] = array
        (
            'handler' => Array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/net/nemein/shoppingcart/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );

        // Handle /
        $this->_request_switch['cart-manage'] = array
        (
            'handler' => Array('net_nemein_shoppingcart_handler_cart', 'manage'),
        );

        // Handle /contents
        $this->_request_switch['cart-view'] = array
        (
            'fixed_args' => array('contents'),
            'handler' => Array('net_nemein_shoppingcart_handler_cart', 'contents'),
        );

        // Handle /xml/manage
        $this->_request_switch['ajax-cart-add'] = array
        (
            'fixed_args' => array('xml', 'manage'),
            'variable_args' => 0,
            'handler' => Array('net_nemein_shoppingcart_handler_cart', 'xml_manage'),
        );

        // Handle /xml/contents
        $this->_request_switch['ajax-cart-contents'] = array
        (
            'fixed_args' => array('xml', 'contents'),
            'variable_args' => 0,
            'handler' => Array('net_nemein_shoppingcart_handler_cart', 'contents'),
        );

        // Handle /add/<guid>
        $this->_request_switch['cart-add'] = array
        (
            'fixed_args' => array('add'),
            'variable_args' => 1,
            'handler' => Array('net_nemein_shoppingcart_handler_cart', 'additem'),
        );

        // Handle /shortlist
        $this->_request_switch['cart-shortlist'] = array
        (
            'fixed_args' => array('shortlist'),
            'handler' => Array('net_nemein_shoppingcart_handler_cart', 'shortlist'),
        );

        // Handle /checkout (redirects to correct handler)
        $this->_request_switch['checkout'] = array
        (
            'fixed_args' => array('checkout'),
            'handler' => Array('net_nemein_shoppingcart_handler_checkout_redirect', 'redirect'),
        );

        // Handle /checkout/null
        $this->_request_switch['checkout-null'] = array
        (
            'fixed_args' => array('checkout', 'null'),
            'handler' => Array('net_nemein_shoppingcart_handler_checkout_null', 'phase1'),
        );

        // Handle /checkout/email
        $this->_request_switch['checkout-email-phase1'] = array
        (
            'fixed_args' => array('checkout', 'email'),
            'handler' => Array('net_nemein_shoppingcart_handler_checkout_email', 'phase1'),
        );
        // Handle /checkout/email-ok
        $this->_request_switch['checkout-email-ok'] = array
        (
            'fixed_args' => array('checkout', 'email-ok'),
            'handler' => Array('net_nemein_shoppingcart_handler_checkout_email', 'phase2'),
        );
    }

    /**
     * Populates the node toolbar depending on the user's rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {   
        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'config/',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                )
            );
        }
        
    }

    /**
     * The handle callback populates the toolbars.
     */
    function _on_handle($handler, $args)
    {
        //$this->_request_data['schemadb'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));

        $this->_populate_node_toolbar();

        return true;
    }

    /**
     * Gets cart items from (or initializes to) session data
     *
     * @return boolean false on critical failure, true otherwise
     */
    function get_items(&$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $session = new midcom_service_session();
        if (!$session->exists("cartdata_{$this->_topic->guid}"))
        {
            debug_add('No shopping cart data in session, creating new one', MIDCOM_LOG_INFO);
            $data['items'] = array();
            $session->set("cartdata_{$this->_topic->guid}", $data['items']);
            debug_pop();
            return true;
        }
        $data['items'] = $session->get("cartdata_{$this->_topic->guid}");
        if (!is_array($data['items']))
        {
            debug_add("FATAL: \$session->get('cartdata_{$this->_topic->guid}') did not return an array", MIDCOM_LOG_ERROR);
            debug_print_r("\$session->get('cartdata_{$this->_topic->guid}') returned: ", $data['items']);
            unset($data['items']);
            debug_pop();
            return false;
        }
        $row_count = count($data['items']);
        debug_add("Found {$row_count} rows in cartdata");
        $data['items_count'] = 0;
        foreach ($data['items'] as $key => $item)
        {
            if (   !isset($item['amount'])
                || !isset($item['product_obj'])
                || !is_object($item['product_obj']))
            {
                debug_add("Item row key '{$key}' has invalid data, clearing", MIDCOM_LOG_ERROR);
                debug_print_r('Invalid row dump: ', $item);
                unset($data['items'][$key]);
                continue;
            }
            $data['items_count'] += $item['amount'];
        }
        debug_pop();
        return true;
    }

    /**
     * Saves schooping cart contents to session data
     *
     * @return boolean indicating success/failure
     */
    function save_items(&$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        // Remove items whose amount is 0 from the list
        foreach ($data['items'] as $key => $item)
        {
            if (   !isset($item['amount'])
                || !isset($item['product_obj'])
                || !is_object($item['product_obj']))
            {
                debug_add("Item row key '{$key}' has invalid data, clearing", MIDCOM_LOG_ERROR);
                debug_print_r('Invalid row dump: ', $item);
                unset($data['items'][$key]);
                continue;
            }
            if (empty($item['amount']))
            {
                debug_add("Product '{$item['product_obj']->code}' amount is empty, removing from cart", MIDCOM_LOG_INFO);
                unset($data['items'][$key]);
            }
        }
        $session = new midcom_service_session();
        $session->set("cartdata_{$this->_topic->guid}", $data['items']);
        // TODO: figure out if we can reliably determine whether this actually gets saved to session data (reading it back will not tell us anything)

        debug_pop();
        return true;
    }
}

?>