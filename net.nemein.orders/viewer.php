<?php
/**
 * @package net.nemein.orders
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Orders Site interface class.
 *
 * @package net.nemein.orders
 */
class net_nemein_orders_viewer {

    var $_debug_prefix;

    var $_config;
    var $_config_dm;
    var $_topic;
    var $_l10n;
    var $_l10n_midcom;
    var $_root_order_event;
    var $_mailing_company_group;
    var $_auth;

    var $_product;
    var $_order;
    var $_cart;

    var $_mode;

    var $errcode;
    var $errstr;


    function net_nemein_orders_viewer($topic, $config) {
        $this->_debug_prefix = "net.nemein.orders.viewer::";

        $this->_config = $config;
        $this->_config_dm = null;
        $this->_topic = $topic;
        $this->_mode = "";
        $this->_auth = null;
        $this->_product = null;
        $this->_order = null;
        $this->_cart = null;

        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("net.nemein.orders");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");

        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";

        $this->_check_root_event();
        $this->_prepare_config_dm();
        $this->_load_mailing_group();
    }

    function get_metadata() {
        return FALSE;
    }

    function can_handle($argc, $argv) {
        /* Don't do this in the constructor, your references will get lost ... */
        $GLOBALS["midcom"]->set_custom_context_data("configuration", $this->_config);
        $GLOBALS["midcom"]->set_custom_context_data("configuration_dm", $this->_config_dm);
        $GLOBALS["midcom"]->set_custom_context_data("l10n", $this->_l10n);
        $GLOBALS["midcom"]->set_custom_context_data("l10n_midcom", $this->_l10n_midcom);
        $GLOBALS["midcom"]->set_custom_context_data("errstr", $this->errstr);
        $GLOBALS["midcom"]->set_custom_context_data("root_order_event", $this->_root_order_event);
        $GLOBALS["midcom"]->set_custom_context_data("mailing_company_group", $this->_mailing_company_group);
        $GLOBALS["midcom"]->set_custom_context_data("product", $this->_product);
        $GLOBALS["midcom"]->set_custom_context_data("order", $this->_order);
        $GLOBALS["midcom"]->set_custom_context_data("auth", $this->_auth);
        $GLOBALS["midcom"]->set_custom_context_data("cart", $this->_cart);

        $this->_auth = new net_nemein_orders_auth();

        if ($argc == 0)
            return TRUE;

        switch($argv[0]){
            case "pub":
                return ($argc == 2);
            case "process_cart":
                return ($argc == 1);
            case "checkout":
                if ($argc < 2)
                    return false;
                switch ($argv[1]) {
                    case "address":
                    case "finalize_order":
                    case "payment":
                    case "process_payment":
                    case "confirm":
                        return ($argc == 2);
                    default:
                        return false;
                }
        }

        return FALSE;
    }


    function handle($argc, $argv) {
        debug_push($this->_debug_prefix . "handle");

        $this->_cart = new net_nemein_orders_cart();

        debug_pop();

        if ($argc == 0) {
            $this->_mode = "welcome";
            return $this->_init_welcome();
        }

        switch($argv[0]){
            case "pub":
                return $this->_init_product_detail($argv[1]);

            case "process_cart":
                return $this->_init_process_cart();

            case "checkout":
                switch ($argv[1]) {
                    case "address":
                        return $this->_init_checkout_address();
                    case "finalize_order":
                        return $this->_init_checkout_finalize_order();
                    case "payment":
                        return $this->_init_checkout_payment();
                    case "process_payment":
                        return $this->_init_checkout_process_payment();
                    case "confirm":
                        return $this->_init_checkout_confirm();

                    default:
                        $this->errcode = MIDCOM_ERRCRIT;
                        $this->errstr = "Method unknown";
                        return TRUE;
                }

            default:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = "Method unknown";
                return TRUE;
        }
    }

    function show() {
        $session = new midcom_service_session();
        if ($session->exists("processing_msg")) {
            midcom_show_style("processing_msg");
            $session->remove("processing_msg");
        }


        eval("\$this->_show_" . $this->_mode . "();");

        return TRUE;
    }

    /******************* CODE INIT/SHOW HELPER METHODS ******************************/

    function _init_welcome() {
        debug_push($this->_debug_prefix . "_init_welcome");
        $this->_mode = "welcome";

        debug_pop();
        return true;
    }

    function _show_welcome() {
        midcom_show_style("heading-product-index");
        midcom_show_style("shopping-cart");
        midcom_show_style("product-index-start");

        $articles = mgd_list_topic_articles($this->_topic->id, "score");
        if ($articles) {
            while ($articles->fetch()) {
                $article = mgd_get_article($articles->id);
                $this->_product = new net_nemein_orders_product($article);
                if (! $this->_product) {
                    debug_add("The product {$article->id} failed to load. Skipping it.", MIDCOM_LOG_INFO);
                    continue;
                }
                if ($this->_product->data["status"] == "offline") {
                    continue;
                }
                midcom_show_style("product-index-item");
            }
        }

        midcom_show_style("product-index-end");
    }


    function _init_product_detail($name) {
        debug_push($this->_debug_prefix . "_init_product_detail");

        $article = mgd_get_article_by_name($this->_topic->id, $name);
        if (! $article) {
            $this->errstr = "This product does not exist: " . mgd_errstr();
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        $this->_product = new net_nemein_orders_product($article);
        if (! $this->_product) {
            debug_add("Could not load the product, last error was {$this->errstr}.", MIDCOM_LOG_WARN);
            $this->errstr = "The product could not be created, this is fatal: " . $this->errstr;
            $this->errcode = MIDCOM_ERRCRIT;
            debug_pop();
            return false;
        }

        $GLOBALS['midcom_component_data']['net.nemein.orders']['active_leaf'] = $article->id;
        $this->_mode = "product_detail";

        debug_pop();
        return true;
    }

    function _show_product_detail() {
        midcom_show_style("heading-product-detail");
        midcom_show_style("shopping-cart");
        midcom_show_style("product-detail");
        midcom_show_style("product-orderform");
    }


    function _init_process_cart() {
        debug_push($this->_debug_prefix . "_init_process_cart");

        if (   ! array_key_exists("form_cart_submit", $_REQUEST)
            || ! array_key_exists("form_action", $_REQUEST)
            || ! array_key_exists("form_returnto", $_REQUEST))
        {
            debug_add("Request incomplete.", MIDCOM_LOG_WARN);
            debug_print_r("Request was:", $_REQUEST);
            $this->errstr = "Request incomplete";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_pop();
            return false;
        }

        switch ($_REQUEST["form_action"]) {
            case "add":
                if (   ! array_key_exists("form_code", $_REQUEST)
                    || ! array_key_exists("form_count", $_REQUEST))
                {
                    debug_add("Request incomplete.", MIDCOM_LOG_WARN);
                    debug_print_r("Request was:", $_REQUEST);
                    $this->errstr = "Request incomplete";
                    $this->errcode = MIDCOM_ERRCRIT;
                    debug_pop();
                    return false;
                }
                $result = $this->_init_process_cart_add($_REQUEST["form_code"], $_REQUEST["form_count"]);
                break;

            case "remove":
                if (   ! array_key_exists("form_code", $_REQUEST))
                {
                    debug_add("Request incomplete.", MIDCOM_LOG_WARN);
                    debug_print_r("Request was:", $_REQUEST);
                    $this->errstr = "Request incomplete";
                    $this->errcode = MIDCOM_ERRCRIT;
                    debug_pop();
                    return false;
                }
                $result = $this->_init_process_cart_remove($_REQUEST["form_code"]);
                break;

            case "remove_all":
                $result = $this->_init_process_cart_remove_all();
                break;

            default:
                $this->errstr = "Method {$_REQUEST['form_action']} unknown";
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;
        }

        if ($result) {
            $GLOBALS["midcom"]->relocate($_REQUEST["form_returnto"]);
        } else {
            return false;
        }

    }

    function _init_process_cart_add ($id, $count)
    {
        $article = mgd_get_article($id);
        if (! $article)
        {
            $this->errstr = "This product does not exist: " . mgd_errstr();
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        $this->_product = new net_nemein_orders_product($article);
        if (! $this->_product)
        {
            debug_add("Could not load the product, last error was {$this->errstr}.", MIDCOM_LOG_WARN);
            $this->errstr = "The product could not be created, this is fatal: " . $this->errstr;
            $this->errcode = MIDCOM_ERRCRIT;
            debug_pop();
            return false;
        }
        $guid = $this->_product->storage->guid();
        sscanf($count, "%d", $quantity);
        if ($quantity <= 0)
        {
            $session = new midcom_service_session();
            $session->set("processing_msg", $this->_l10n->get("you must enter a positive quantity."));
            /* This will return to whence we came, but with an error message. */
        }
        else if (! $this->_cart->add_item($guid, $quantity))
        {
            debug_add("Could not update shopping cart.", MIDCOM_LOG_WARN);
            $this->errstr = "The shopping cart could not be updated.";
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }
        return true;
    }

    function _init_process_cart_remove ($id)
    {
        $article = mgd_get_article($id);
        if (! $article)
        {
            $this->errstr = "This product does not exist: " . mgd_errstr();
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        $this->_product = new net_nemein_orders_product($article);
        if (! $this->_product)
        {
            debug_add("Could not load the product, last error was {$this->errstr}.", MIDCOM_LOG_WARN);
            $this->errstr = "The product could not be created, this is fatal: " . $this->errstr;
            $this->errcode = MIDCOM_ERRCRIT;
            debug_pop();
            return false;
        }
        $guid = $this->_product->storage->guid();

        if (! $this->_cart->remove_item($guid))
        {
            debug_add("Could not update shopping cart, requested item was not in the cart.", MIDCOM_LOG_WARN);
            $this->errstr = "The shopping cart could not be updated.";
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }
        return true;
    }

    function _init_process_cart_remove_all () {
        if (! $this->_cart->remove_all()) {
            debug_add("Could not update shopping cart, requested item was not in the cart.", MIDCOM_LOG_WARN);
            $this->errstr = "The shopping cart could not be updated.";
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }
        return true;
    }


    function _init_checkout_address() {
        debug_push($this->_debug_prefix . "_init_checkout_address");

        /* First, fire up sessioning to make the guid of the active article transient */
        $session = new midcom_service_session();

        if (! $session->exists("create_order_guid")) {
            debug_add("We don't have a content object yet, setting fresh-created mode.");
            $this->_order = new net_nemein_orders_order();
            $create = true;
        } else {
            $guid = $session->get("create_order_guid");
            debug_add("We do have the guid [$guid], trying to load.");
            $event = mgd_get_object_by_guid($guid);
            if (   ! $event
                || $event->__table__ != "event"
                || $event->up != $this->_root_order_event->id
                || $event->type != 3)
            {
                $this->errstr = "Invalid GUID, could not enter creation mode. See log for details.";
                $this->errcode = MIDCOM_ERRNOTFOUND;
                debug_add("GUID $guid invalid for creation mode, could be: guid nonexistant, no event or wrong root event.",
                          MIDCOM_LOG_WARN);
                debug_print_r("Retrieved object:", $event);
                return false;
            }
            $this->_order = new net_nemein_orders_order($event);
            $create = false;
        }
        if (! $this->_order) {
            $this->errstr = "Order could not be loaded: " . $this->errstr;
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add("Order could not be loaded, this usually indicates a datamanager problem.");
            return false;
        }
        if ($create && ! $this->_order->init_create()) {
            $this->errstr = "Order could not be set into creation mode: " . $this->errstr;
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add("Order could not be into creation mode, this usually indicates a datamanager problem.");
            return false;
        }

        $this->_su();
        $result = $this->_order->datamanager->process_form();
        $this->_su(false);

        $this->_mode = "checkout_address";

        switch ($result) {
            case MIDCOM_DATAMGR_CREATING:
                if (! $create) {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = "Method MIDCOM_DATAMANAGER_CREATING unknown for non-creation mode.";
                    debug_pop();
                    return false;
                } else {
                    /* First call, display from. */
                    debug_add("First call within creation mode");
                    break;
                }

            case MIDCOM_DATAMGR_EDITING:
                if ($create) {
                    $guid = $this->_order->storage->guid();
                    debug_add("First time submit, the DM has created an object, adding GUID [$guid] to session data");
                    $session->set("create_order_guid", $guid);
                } else {
                    debug_add("Subsequent submit, we already have a guid in the session space.");
                }
                break;

            case MIDCOM_DATAMGR_SAVED:
                debug_add("Datamanger has saved, relocating to view.");
                if ($session->exists("create_order_guid")) {
                    $session->set("order_guid", $session->remove("create_order_guid"));
                } else {
                    $session->set("order_guid", $this->_order->storage->guid());
                }
                $this->_relocate("checkout/finalize_order.html");
                /* This will exit() */

            case MIDCOM_DATAMGR_CANCELLED_NONECREATED:
                if (! $create) {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = "Method MIDCOM_DATAMGR_CANCELLED_NONECREATED unknown for non-creation mode.";
                    debug_pop();
                    return false;
                } else {
                    debug_add("Cancel without anything being created, redirecting to the welcome screen.");
                    $this->_relocate("");
                    /* This will exit() */
                }

            case MIDCOM_DATAMGR_CANCELLED:
                if ($create) {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = "Method MIDCOM_DATAMGR_CANCELLED unknown for creation mode.";
                    debug_pop();
                    return false;
                } else {
                    debug_add("Cancel with a temporary object, deleting it and redirecting to the welcome screen.");
                    $this->_order->delete;
                    $this->_relocate("");
                    /* This will exit() */
                }

            case MIDCOM_DATAMGR_FAILED:
            case MIDCOM_DATAMGR_CREATEFAILED:
                debug_add("The DM failed critically, see above.");
                $this->errstr = "The Datamanger failed to process the request, see the Debug Log for details";
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;

            default:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = "Method unknown";
                debug_pop();
                return false;
        }

        debug_pop();
        return true;
    }

    function _show_checkout_address () {
        midcom_show_style("heading-checkout-address");
        midcom_show_style("dmform-checkout-order");
    }


    function _init_checkout_finalize_order() {
        debug_push($this->_debug_prefix . "_init_checkout_finalize_order");

        $session = new midcom_service_session();

        if (! $session->exists("order_guid")) {
            debug_add("Session does not contain the key order_guid", MIDCOM_LOG_INFO);
            $this->errstr = "Your session is probably expired, the key order_guid is missing.";
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }
        $event = mgd_get_object_by_guid($session->get("order_guid"));
        if (   ! $event
            || $event->__table__ != "event"
            || $event->up != $this->_root_order_event->id
            || $event->type != 3)
        {
            debug_add("GUID order_guid in the session data is invalid.", MIDCOM_LOG_INFO);
            debug_print_r("Retrieved object:", $event);
            $this->errstr = "Session data invalid, order_guid is wrong.";
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }
        $this->_order = new net_nemein_orders_order($event);
        if (! $this->_order) {
            $this->errstr = "Order could not be loaded: " . $this->errstr;
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add("Order could not be loaded, this usually indicates a datamanager problem.");
            return false;
        }

        $cart = $this->_cart->get_cart();
        foreach ($cart as $guid => $item) {
            $this->_order->set_order($item["product"], $item["quantity"]);
        }

        $this->_su();
        $this->_order->save_orders();
        $this->_su(false);

        $this->_mode = "checkout_finalize_order";

        debug_pop();
        return true;
    }

    function _show_checkout_finalize_order() {
        midcom_show_style("heading-checkout-finalize");
        midcom_show_style("dmview-checkout-order");
        midcom_show_style("checkout-orderitems-begin");
        $orders = $this->_order->get_order();
        foreach ($orders as $guid => $item) {
            $GLOBALS["view_item"] = $item;
            midcom_show_style("checkout-orderitems-item");
        }
        midcom_show_style("checkout-orderitems-end");

        midcom_show_style("checkout-confirmation-buttons");
    }

    /**
     * Init handler which processes a payment executed by one of the n.n.payment
     * handlers.
     *
     * The init handler will load the library.
     *
     * There is no output handler accociated with this handler, either an error is triggered,
     * or the user is relocated accordingly. The cancel handler is used for both rejections and
     * cancellations in conjunction with a processing message.
     *
     * @return bool Indicating success.
     */
    function _init_checkout_process_payment()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (! $this->_config->get('enable_net_nemein_payment_integration'))
        {
            debug_add("n.n.payments integration is not activated, accessing payment.html is forbidden.", MIDCOM_LOG_INFO);
            $this->errstr = "n.n.payments integration is not activated, accessing this page is forbidden.";
            $this->errcode = MIDCOM_ERRFORBIDDEN;
            debug_pop();
            return false;
        }

        if (! array_key_exists('order_id', $_REQUEST))
        {
            debug_add("payment request headers are incomplete, cannot proceed.", MIDCOM_LOG_INFO);
            $this->errstr = "payment request headers are incomplete, cannot proceed.";
            $this->errcode = MIDCOM_ERRFORBIDDEN;
            debug_pop();
            return false;
        }

        $session = new midcom_service_session();

        // Be error resilent against session timeouts when returning.
        if (! $session->exists("order_guid"))
        {
            debug_add("Session does not contain the key order_guid", MIDCOM_LOG_INFO);
            debug_add("The session probably did expire, recovering through the return URL.");
            $event = mgd_get_event($_REQUEST['order_id']);
            if (! $event)
            {
                debug_add("payment request headers are invalid, order was not found, cannot proceed.", MIDCOM_LOG_INFO);
                debug_add("Last error was: " . mgd_errstr);
                $this->errstr = "payment request headers are invalid, order was not found, cannot proceed: " . mgd_errstr() ;
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;
            }
            $session->set('order_guid', $event->guid);
        }
        else
        {
            $event = mgd_get_object_by_guid($session->get("order_guid"));
        }

        if (   ! $event
            || $event->__table__ != "event"
            || $event->up != $this->_root_order_event->id)
        {
            debug_add("GUID order_guid in the session data is invalid.", MIDCOM_LOG_INFO);
            debug_print_r("Retrieved object:", $event);
            $this->errstr = "Session data invalid, order_guid is wrong.";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_pop();
            return false;
        }
        if ($event->id != $_REQUEST['order_id'])
        {
            debug_add("Payment request headers are invalid, Event ID did not match the Session Event ID, possible tampering, so we abort.", MIDCOM_LOG_INFO);
            debug_print_r("Request was:", $_REQUEST);
            debug_print_r("Retreived event was:", $event);
            $this->errstr = "Payment request headers are invalid, Event ID did not match the Session Event ID, possible tampering, so we abort.";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_pop();
            return false;
        }
        if ($event->type != 3)
        {
            debug_add("The order referenced in the session has alreadey been processed.", MIDCOM_LOG_INFO);
            debug_print_r("Retrieved object:", $event);
            $this->errstr = "Session data invalid, this order has already been processed.";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_pop();
            return false;
        }

        $this->_order = new net_nemein_orders_order($event);
        if (! $this->_order)
        {
            $this->errstr = "Order could not be loaded: " . $this->errstr;
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add("Order could not be loaded, this usually indicates a datamanager problem.");
            debug_pop();
            return false;
        }

        debug_add("Loading the payment lib and processing the payment");
        $GLOBALS['midcom']->load_library('net.nemein.payment');

        $payment =& net_nemein_payment_factory::get_instance();
        $handler =& $payment->process_payment();

        switch ($handler->result)
        {
            case NET_NEMEIN_PAYMENT_OK:
                // Store the payment information at the order.
                $this->_order->storage->parameter('midcom.helper.datamanager', 'data_epayment_state', $handler->message);
                $this->_order->storage->parameter('midcom.helper.datamanager', 'data_epayment_data', serialize($handler->machine_response));
                $this->_order->storage->parameter('midcom.helper.datamanager', 'data_paid', time());

                // Relocate to the order confirmation page.
                $this->_relocate('checkout/confirm.html?form_order_confirm=ok');
                // This will exit

            case NET_NEMEIN_PAYMENT_OK_BUT_PENDING:
                // Store the payment information at the order.
                $this->_order->storage->parameter('midcom.helper.datamanager', 'data_epayment_state', $handler->message);
                $this->_order->storage->parameter('midcom.helper.datamanager', 'data_epayment_data', serialize($handler->machine_response));

                // Relocate to the order confirmation page.
                $this->_relocate('checkout/confirm.html?form_order_confirm=ok');
                // This will exit

            case NET_NEMEIN_PAYMENT_REJECTED:
                // Tell the user that the payment was rejected, then treat it as a cancelled
                // order.
                $session->set('processing_msg', $handler->message);
                // FALL THROUGH

            case NET_NEMEIN_PAYMENT_CANCELLED:
                debug_add("Order has been cancelled or rejected, we delete the order and remove the session key. Cart stays.");
                $this->_cancel_order($session);
                $this->_relocate('');
                // This will exit()
        }

        debug_pop();
        return true;
    }

    /**
     * Init handler which prepares for payment of a previously confirmed order.
     * If n.n.payment integration is not activated, execution will halt.
     *
     * The init handler will load the library.
     *
     * This will always execute _show_checkout_payment()
     *
     * @return bool Indicating success.
     */
    function _init_checkout_payment()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (! $this->_config->get('enable_net_nemein_payment_integration'))
        {
            debug_add("n.n.payments integration is not activated, accessing payment.html is forbidden.", MIDCOM_LOG_INFO);
            $this->errstr = "n.n.payments integration is not activated, accessing this page is forbidden.";
            $this->errcode = MIDCOM_ERRFORBIDDEN;
            debug_pop();
            return false;
        }

        $session = new midcom_service_session();

        if (! $session->exists("order_guid"))
        {
            debug_add("Session does not contain the key order_guid", MIDCOM_LOG_INFO);
            $this->errstr = "Your session is probably expired, the key order_guid is missing.";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_pop();
            return false;
        }

        $event = mgd_get_object_by_guid($session->get("order_guid"));
        if (   ! $event
            || $event->__table__ != "event"
            || $event->up != $this->_root_order_event->id)
        {
            debug_add("GUID order_guid in the session data is invalid.", MIDCOM_LOG_INFO);
            debug_print_r("Retrieved object:", $event);
            $this->errstr = "Session data invalid, order_guid is wrong.";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_pop();
            return false;
        }
        if ($event->type != 3)
        {
            debug_add("The order referenced in the session has alreadey been processed.", MIDCOM_LOG_INFO);
            debug_print_r("Retrieved object:", $event);
            $this->errstr = "Session data invalid, this order has already been processed.";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_pop();
            return false;
        }
        $this->_order = new net_nemein_orders_order($event);
        if (! $this->_order)
        {
            $this->errstr = "Order could not be loaded: " . $this->errstr;
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add("Order could not be loaded, this usually indicates a datamanager problem.");
            debug_pop();
            return false;
        }

        // Check, wether we have an amount > 0
        // If yes, don't invoke payment but finalize directly.
        $totals = $this->_order->get_totals();
        if ($totals['sum'] == 0)
        {
            // Store the payment information at the order.
            $this->_order->storage->parameter('midcom.helper.datamanager', 'data_epayment_state', 'No payment done as the amount was 0.00.');
            $this->_order->storage->parameter('midcom.helper.datamanager', 'data_epayment_data', serialize('No payment done as the amount was 0.00.'));
            $this->_order->storage->parameter('midcom.helper.datamanager', 'data_paid', time());

            // Relocate to the order confirmation page.
            $this->_relocate('checkout/confirm.html?form_order_confirm=ok');
            // This will exit
        }

        $GLOBALS['midcom']->load_library('net.nemein.payment');

        $this->_mode = "checkout_payment";

        debug_pop();
        return true;
    }

    /**
     * Displays the order and the list of payment handlers.
     *
     * Style elements in use (in this order):
     *
     * - heading-checkout-payment
     * - dmview-checkout-order
     * - checkout-orderitems-begin
     * - checkout-orderitems-item (once for each ordered product)
     * - checkout-orderitems-end
     * - checkout-payment-options
     *
     * The payment options will lead to the pages of the payment processing system, which
     * will then relocate to the checkout/process_payment handler.
     */
    function _show_checkout_payment()
    {
        midcom_show_style("heading-checkout-payment");
        midcom_show_style("dmview-checkout-order");
        midcom_show_style("checkout-orderitems-begin");
        $orders = $this->_order->get_order();
        foreach ($orders as $guid => $item) {
            $GLOBALS["view_item"] = $item;
            midcom_show_style("checkout-orderitems-item");
        }
        midcom_show_style("checkout-orderitems-end");

        midcom_show_style("checkout-payment-options");
    }

    function _init_checkout_confirm() {
        debug_push($this->_debug_prefix . "_init_checkout_confirm");

        $session = new midcom_service_session();

        if (! $session->exists("order_guid")) {
            debug_add("Session does not contain the key order_guid", MIDCOM_LOG_INFO);
            $this->errstr = "Your session is probably expired, the key order_guid is missing.";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_pop();
            return false;
        }
        $event = mgd_get_object_by_guid($session->get("order_guid"));
        if (   ! $event
            || $event->__table__ != "event"
            || $event->up != $this->_root_order_event->id)
        {
            debug_add("GUID order_guid in the session data is invalid.", MIDCOM_LOG_INFO);
            debug_print_r("Retrieved object:", $event);
            $this->errstr = "Session data invalid, order_guid is wrong.";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_pop();
            return false;
        }
        if ($event->type != 3) {
            debug_add("The order referenced in the session has alreadey been processed.", MIDCOM_LOG_INFO);
            debug_print_r("Retrieved object:", $event);
            $this->errstr = "Session data invalid, this order has already been processed.";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_pop();
            return false;
        }
        $this->_order = new net_nemein_orders_order($event);
        if (! $this->_order) {
            $this->errstr = "Order could not be loaded: " . $this->errstr;
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add("Order could not be loaded, this usually indicates a datamanager problem.");
            debug_pop();
            return false;
        }

        if (array_key_exists("form_order_confirm", $_REQUEST)) {
            /*** ORDER CONFIRMED ***/
            $this->_mode = "checkout_confirm";

            $this->_su();


            debug_add("Updating products: Reserving Items...");
            $items = $this->_order->get_order();
            $item_string = "-------------------------------------------------------\n";

            foreach ($items as $guid => $item) {
                if (! $item["product"]->reserve_items($item["quantity"])) {

                    $this->_order->storage->type = $this->_order->_ordertype_corrupt;
                    if (! $this->_order->storage->update()) {
                        debug_add("Could not set event $guid to state corrupt!", MIDCOM_LOG_CRIT);
                    }

                    /* errstr is set by reserve_items */
                    debug_add("Could not reserve {$items['quantity']} items for product "
                        . $items['product']->storage->guid(), MIDCOM_LOG_ERROR);
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->_su(false);
                    debug_pop();
                    return false;
                }

                debug_add("Reserved {$item['quantity']} items for product "
                    . $item['product']->storage->guid(), MIDCOM_LOG_ERROR);

                $item_string .= $item["product"]->data["title"] . "\n";
                $item_string .= "    " . $this->_l10n->get("quantity") . ": " . $item["quantity"] . "\n";
                $item_string .= "    " . $this->_l10n->get("free copies") . ": " . $item["free_quantity"] . "\n";
                $item_string .= "    " . $this->_l10n->get("billable copies") . ": " . $item["pay_quantity"] . "\n";
                $item_string .= "    " . $this->_l10n->get("net sum") . ": "
                    . number_format($item["sum_net"], 2, ".", " ") . " " . $this->_config->get("currency_sign") . "\n";
                $item_string .= "    " . $this->_l10n->get("vat") . " " . ": "
                    . number_format($item["vat"], 2, ".", " ") . " " . $this->_config->get("currency_sign") . "\n";
                $item_string .= "    " . $this->_l10n->get("gross sum") . ": "
                    . number_format($item["sum"], 2, ".", " ") . " " . $this->_config->get("currency_sign") . "\n\n";
            }

            $totals = $this->_order->get_totals();
            $item_string .= "-------------------------------------------------------\n";
            if ($totals['shipping'])
            {
                $item_string .= $this->_l10n->get('shipping:') . ' ' . number_format($totals['shipping'], 2, '.', ' ')
                    . ' ' . $this->_config->get('currency_sign') . "\n";
            }
            $item_string .= $this->_l10n->get("grand total:") . " " . number_format($totals["sum"], 2, ".", " ")
                . " " . $this->_config->get("currency_sign") . "\n";
            $item_string .= "-------------------------------------------------------\n";

            debug_add("Order confirmed, updating the order record.");
            $this->_order->storage->type = $this->_order->_ordertype_pending;
            if (! $this->_order->storage->update()) {
                $this->errstr = "Could not update order: " . mgd_errstr;
                $this->errcode = MIDCOM_ERRCRIT;
                $this->_su(false);
                debug_pop();
                return false;
            }

            $this->_su(false);

            debug_add("Parsing E-Mail Template");
            $template = new midcom_helper_mailtemplate($this->_config_dm->data["mail_orderconfirm"]);
            $parameters = Array(
                "ORDER" => $this->_order->datamanager,
                "ITEMS" => $item_string,
            );
            $template->set_parameters($parameters);
            $template->parse();
            $failed = $template->send($this->_order->data["email"]);

            if ($failed > 0) {
                debug_add("$failed E-Mails could not be sent.", MIDCOM_LOG_WARN);
                debug_print_r("Failed addresses:", $template->failed);
            }
            debug_print_r("Successful addresses:", $template->success);
            debug_add("Order successfully processed, cleaning up shopping cart.");

            $this->_cart->remove_all();

        } else if (array_key_exists("form_order_cancel", $_REQUEST)) {
            /*** ORDER CANCELLED ***/
            debug_add("Order has been cancelled, we delete the order and remove the session key. Cart stays.");
            $this->_cancel_order($session);
            $this->_relocate('');
            /* This will exit() */

        } else {
            /*** UNKNOWN REQUEST ***/
            debug_add("Request seems incomplete.", MIDCOM_LOG_INFO);
            debug_print_r("Request was:", $_REQUEST);
            $this->errstr = "Incomplete Request.";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_pop();
            return false;
        }

        $this->_mode = "checkout_confirm";

        debug_pop();
        return true;
    }

    function _show_checkout_confirm() {
        midcom_show_style("checkout-confirm");
    }

    /************** PRIVATE HELPER METHODS **********************/

    /**
     * Cancelles the currently processed order and clears the session.
     *
     * @param midcom_helper_session $session The session that should be cleared.
     * @see $_order
     * @access private
     */
    function _cancel_order($session)
    {
        $this->_su();
        $this->_order->delete();
        $this->_su(false);
        $session->remove("order_guid");
    }

    function _su ($on = true) {
        if ($on) {
            if (! mgd_auth_midgard ($this->_config->get("admin_user"), $this->_config->get("admin_password"), false))
            {
                $GLOBALS['midcom']->generate_error(MIDCOM_ERRFORBIDDEN,
                    "mgd_auth_midgard to admin level user failed.");
            } else {
                debug_print_r("New midgard object is: ", mgd_get_midgard());
                /* Call mgd_get_midgard, seems to be required according to emile/piotras */
                $unused = mgd_get_midgard();
                return true;
            }
        } else {
            $result = mgd_unsetuid();
            /* Call mgd_get_midgard, seems to be required according to emile/piotras */
            $unused = mgd_get_midgard();
            return $result;
        }
    }

    function _relocate ($url) {
        $GLOBALS["midcom"]->relocate(
              $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
            . $url);
    }

    function _prepare_config_dm ()
    {
        debug_push($this->_debug_prefix . "_prepare_config_dm");

        /* Set a global so that the schema gets internationalized */
        $GLOBALS["view_l10n"] = $this->_l10n;
        $this->_config_dm = new midcom_helper_datamanager("file:/net/nemein/orders/config/schemadb_config.inc");

        if ($this->_config_dm == false)
        {
            debug_add("Failed to instantinate configuration datamanager.", MIDCOM_LOG_CRIT);
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                                               "Failed to instantinate configuration datamanager.");
        }

        // Load the configuration topic
        if ($this->_config->get('symlink_config_topic'))
        {
            $config_topic = $this->_config->get('symlink_config_topic');
        }
        else
        {
            $config_topic = $this->_topic;
        }

        if (! $this->_config_dm->init($this->_topic))
        {
            debug_add("Failed to initialize the datamanager.", MIDCOM_LOG_CRIT);
            debug_print_r("Topic object we tried was:", $this->_topic);
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                                               "Failed to initialize configuration datamanager.");
        }


        debug_pop();
        return;
    }

    function _load_mailing_group() {
        $guid = $this->_config->get("mailing_company_group");
        if (is_null($guid)) {
            $this->_mailing_company_group = null;
        } else {
            $grp = mgd_get_object_by_guid($guid);
            if (! $grp || $grp->__table__ != "grp") {
                debug_add("Could not load Mailing company group, invalid GUID detected: " . mgd_errstr(), MIDCOM_LOG_WARN);
                debug_print_r("Retrieved object was:", $grp);
                $this->_mailing_company_group = null;
            } else {
                $this->_mailing_company_group = $grp;
            }
        }
    }

    function _check_root_event() {
        debug_push($this->_debug_prefix . "_check_root_event");
        if (is_null ($this->_config->get("root_order_event"))) {
            $GLOBALS["midcom"]->generate_error(
                "The root event has not yet been set, please load AIS and configure this topic",
                MIDCOM_ERRCRIT);
        } else {
            debug_add("Trying to load the root event [" . $this->_config->get("root_order_event") . "]");
            $event = mgd_get_object_by_guid($this->_config->get("root_order_event"));
            if (! $event || $event->__table__ != "event") {
                debug_add("Failed to load root event, the GUID references a non-existant or invalid object.", MIDCOM_LOG_ERROR);
                debug_print_r("Retrieved object was:", $event);
                $GLOBALS["midcom"]->generate_error(
                    "The root event is invalid, please check the topic configuration",
                    MIDCOM_ERRCRIT);
            }
            $this->_root_order_event = $event;
            debug_pop();
        }
    }

} // viewer

?>