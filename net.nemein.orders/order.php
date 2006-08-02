<?php
/**
 * @package net.nemein.orders
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Orders Order class.
 *
 * @package net.nemein.orders
 */
class net_nemein_orders_order extends net_nemein_orders__base {

    var $datamanager;
    var $data;
    var $storage;
    var $_order_items;

    function net_nemein_orders_order($event = null) {

        /* Initialize base class */
        parent::net_nemein_orders__base();

        /* Initialize ourself */
        $this->_order_items = Array();

        if (is_null($event)) {
            $this->storage = null;
            if (! $this->_init_creation_mode()) {
                $x =& $this;
            	$x = false;
                return false;
            }
        } else {
            $this->storage = $event;
            if (! $this->_load_from_storage()) {
                $x =& $this;
            	$x = false;
                return false;
            }
        }

    }

    function init_create() {
        if (! $this->datamanager->init_creation_mode($this->_config->get("schema_order"), $this)) {
            debug_add("order::init_create failed to prepare the creation datamanager, see above.");
            return false;
        }
        return true;
    }

    /**************** Midgard IO Overloads **************/

    function delete() {
        if (is_null($this->storage)) {
            debug_add("Can't delete, we don't have an object to. Failing silently.");
            return true;
        }

        if ($this->storage->type == $this->_ordertype_pending) {
            foreach ($this->_order_items as $guid => $item) {
                if (! $item["product"]->cancel_reserved_items($item["quantity"])) {
                    $this->storage->type = $this->_ordertype_corrupt;
                    $this->storage->update();
                    $this->_errstr = "Failed to update the products accociated with the order. Possible data corruption";
                    debug_pop();
                    return false;
                }
            }
        }

        if (! mgd_delete_extensions($this->storage)) {
            debug_add("Failed to delete extensions: " . mgd_errstr());
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                "Failed to delete the storage object extensions: " . mgd_errstr());
        }
        if (! $this->storage->delete()) {
            debug_add("Failed to delete the object: " . mgd_errstr());
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                "Failed to delete the storage object: " . mgd_errstr());
        }
        return true;
    }

    /**************** Product Order Interface Functions ***************/

    function add_order ($product, $total_quantity, $free_quantity = null) {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Setting product " . $product->data["code"] . " to $total_quantity items.");
        debug_print_r("Product storage object:", $product->storage);
        debug_add("Free items are set to " . (is_null ($free_quantity) ? "NULL" : $free_quantity));
        $guid = $product->storage->guid();
        $total_quantity = (integer) $total_quantity;
        if (! is_null($free_quantity))
        {
            $free_quantity = (integer) $free_quantitiy;
        }
        if (array_key_exists($guid, $this->_order_items))
        {
            $this->_order_items[$guid]["quantity"] += $total_quantity;
            if (is_null($free_quantity))
            {
                /* No addition here, the call will always return the maximum number of free
                 * items.
                 */
                $this->_order_items[$guid]["free_quantity"] = min($product->get_remaining_free_items($this), $total_quantity);
            }
            else
            {
               $this->_order_items[$guid]["free_quantity"] += min($free_quantity, $total_quantity);
            }
            $this->_order_items[$guid]["pay_quantity"] = $this->_order_items[$guid]["quantity"]
                - $this->_order_items[$guid]["free_quantity"];
        }
        else
        {
            $this->_order_items[$guid]["product"] = $product;
            $this->_order_items[$guid]["free_quantity"] =
                min((is_null($free_quantity) ? $product->get_remaining_free_items($this) : $free_quantity),
                    $total_quantity);
            $this->_order_items[$guid]["pay_quantity"] = $total_quantity - $this->_order_items[$guid]["free_quantity"];
            $this->_order_items[$guid]["quantity"] = $total_quantity;
        }
        $this->_order_items[$guid]["sum_net"] = round($this->_order_items[$guid]["pay_quantity"] * $product->data["price"], 2);
        $this->_order_items[$guid]["vat"] = round($this->_order_items[$guid]["sum_net"] * $product->data["vat"] / 100, 2);
        $this->_order_items[$guid]["sum"] = $this->_order_items[$guid]["sum_net"] + $this->_order_items[$guid]["vat"];

        debug_add("Element $guid, free_quantity is now: " . $this->_order_items[$guid]["free_quantity"]);
        debug_add("Element $guid, pay_quantity is now : " . $this->_order_items[$guid]["pay_quantity"]);
        debug_add("Element $guid, quantity is now     : " . $this->_order_items[$guid]["quantity"]);
        debug_add("Element $guid, sum_net is now      : " . $this->_order_items[$guid]["sum_net"]);
        debug_add("Element $guid, vat is now          : " . $this->_order_items[$guid]["vat"]);
        debug_add("Element $guid, sum is now          : " . $this->_order_items[$guid]["sum"]);
        debug_pop();
    }

    function set_order ($product, $total_quantity, $free_quantity = null) {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Setting product " . $product->data["code"] . " to $total_quantity items.");
        debug_print_r("Product storage object:", $product->storage);
        debug_add("Free items are set to " . (is_null ($free_quantity) ? "NULL" : $free_quantity));
        $guid = $product->storage->guid();
        $total_quantity = (integer) $total_quantity;
        if (! is_null($free_quantity))
            $free_quantity = (integer) $free_quantitiy;
        if (! array_key_exists($guid, $this->_order_items)) {
            $this->_order_items[$guid]["product"] = $product;
        }
        $this->_order_items[$guid]["free_quantity"] =
            min((is_null($free_quantity) ? $product->get_remaining_free_items($this) : $free_quantity),
                $total_quantity);
        $this->_order_items[$guid]["pay_quantity"] = $total_quantity - $this->_order_items[$guid]["free_quantity"];
        $this->_order_items[$guid]["quantity"] = $total_quantity;
        $this->_order_items[$guid]["sum_net"] = round($this->_order_items[$guid]["pay_quantity"] * $product->data["price"], 2);
        $this->_order_items[$guid]["vat"] = round($this->_order_items[$guid]["sum_net"] * $product->data["vat"] / 100, 2);
        $this->_order_items[$guid]["sum"] = $this->_order_items[$guid]["sum_net"] + $this->_order_items[$guid]["vat"];

        debug_add("Element $guid, free_quantity is now: " . $this->_order_items[$guid]["free_quantity"]);
        debug_add("Element $guid, pay_quantity is now : " . $this->_order_items[$guid]["pay_quantity"]);
        debug_add("Element $guid, quantity is now     : " . $this->_order_items[$guid]["quantity"]);
        debug_add("Element $guid, sum_net is now      : " . $this->_order_items[$guid]["sum_net"]);
        debug_add("Element $guid, vat is now          : " . $this->_order_items[$guid]["vat"]);
        debug_add("Element $guid, sum is now          : " . $this->_order_items[$guid]["sum"]);
        debug_pop();
    }

    function remove_order ($product) {
        debug_add("Removing product " . $product->data["code"] . "from order.");
        $guid = $product->storage->guid();
        if (! array_key_exists($guid, $this->_order_items)) {
            debug_add("This product was not a part of this order, request ignored, as the effect is the same for the user.");
        } else {
            unset($this->_order_items[$guid]);
        }
    }

    function get_order() {
        return $this->_order_items;
    }

    function save_orders() {
        return $this->_save_orders_to_storage();
    }

    function load_orders() {
        $this->_load_orders_from_storage();
    }

    function get_totals() {
        $grand_total = 0;
        $vat_sum = 0;
        $net_sum = 0;
        foreach ($this->_order_items as $guid => $item) {
            $grand_total += $item["sum"];
            $vat_sum += $item["vat"];
            $net_sum += $item["sum_net"];
        }
        $shipping = $this->_config->get('shipping');
        $grand_total += $shipping;
        return Array (
            "sum_net" => $net_sum,
            "vat" => $vat_sum,
            "shipping" => $shipping,
            "sum" => $grand_total,
        );
    }

    /**************** Product Interface Functions ***************/

    function mark_as_sent() {
        $item_string = "-------------------------------------------------------\n";
        foreach ($this->_order_items as $guid => $item)
        {
            if (! $item["product"]->deliver_items($item["quantity"]))
            {
                $this->storage->type = $this->_ordertype_corrupt;
                $this->storage->update();
                $this->_errstr = "Failed to update the products accociated with the order. Possible data corruption";
                debug_pop();
                return false;
            }
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

        $totals = $this->get_totals();
        $item_string .= "-------------------------------------------------------\n";
        if ($totals['shipping'])
        {
            $item_string .= $this->_l10n->get('shipping:') . ' ' . number_format($totals['shipping'], 2, '.', ' ')
                . ' ' . $this->_config->get('currency_sign') . "\n";
        }
        $item_string .= $this->_l10n->get("grand total:") . " " . number_format($totals["sum"], 2, ".", " ")
            . " " . $this->_config->get("currency_sign") . "\n";
        $item_string .= "-------------------------------------------------------\n";


        $this->storage->type = $this->_ordertype_delivered;
        if (! $this->storage->update())
        {
            $this->storage->type = $this->_ordertype_corrupt;
            $this->storage->update();
            $this->_errstr = "Could not set order state to delivered:" . mgd_errstr();
            return false;
        }

        debug_add("Parsing E-Mail Template");
        // Reinit the datamanager to ensure current information (ordertype has changed)
        $this->datamanager->init($this->storage);
        $this->data =& $this->datamanager->data;
        $template = new midcom_helper_mailtemplate($this->_config_dm->data["mail_ordersent"]);
        $parameters = Array(
            "ORDER" => $this->datamanager,
            "ITEMS" => $item_string,
        );
        $template->set_parameters($parameters);
        $template->parse();
        $failed = $template->send($this->data["email"]);

        if ($failed > 0) {
            debug_add("$failed E-Mails could not be sent.", MIDCOM_LOG_WARN);
            debug_print_r("Failed addresses:", $template->failed);
        }
        debug_print_r("Successful addresses:", $template->success);


        $this->storage->parameter("midcom.helper.datamanager", "data_order_sent", time());

        return true;
    }

    function mark_as_unsent() {
        foreach ($this->_order_items as $guid => $item) {
            if (! $item["product"]->undeliver_items($item["quantity"])) {
                $this->storage->type = $this->_ordertype_corrupt;
                $this->storage->update();
                $this->_errstr = "Failed to update the products accociated with the order. Possible data corruption";
                debug_pop();
                return false;
            }
        }

        $this->storage->type = $this->_ordertype_pending;
        if (! $this->storage->update())
        {
            $this->storage->type = $this->_ordertype_corrupt;
            $this->storage->update();
            $this->_errstr = "Could not set order state to not delivered:" . mgd_errstr();
            return false;
        }

        $this->storage->parameter("midcom.helper.datamanager", "data_order_sent", "");

        return true;
    }



    /**************** Internal Helper Functions ***************/

    function _check_storage()
    {
        if (is_null($this->storage))
        {
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                "Tried to access a nonexisting storage object.");
        }
    }

    /**
     * Patches the currently active datamanager layout database so that the
     * various payment fields of the order schema has the correct visibility
     * settings.
     */
    function _patch_schema()
    {
        if ($this->_config->get('enable_payment_management'))
        {
            $this->datamanager->_layoutdb[$this->_config->get('schema_order')]['fields']['paid']['hidden'] = false;
            if ($this->_auth->is_poweruser())
            {
                $this->datamanager->_layoutdb[$this->_config->get('schema_order')]['fields']['paid']['readonly'] = false;
            }
        }
        if ($this->_config->get('enable_net_nemein_payment_integration'))
        {
            $this->datamanager->_layoutdb[$this->_config->get('schema_order')]['fields']['epayment_state']['hidden'] = false;
            if ($this->_auth->is_admin())
            {
                $this->datamanager->_layoutdb[$this->_config->get('schema_order')]['fields']['epayment_state']['readonly'] = false;
                $this->datamanager->_layoutdb[$this->_config->get('schema_order')]['fields']['epayment_data']['hidden'] = false;
                $this->datamanager->_layoutdb[$this->_config->get('schema_order')]['fields']['epayment_data']['readonly'] = false;
            }
        }

    }

    function _init_creation_mode()
    {
        $GLOBALS["view_l10n"] = $this->_l10n;
        $this->datamanager = new midcom_helper_datamanager($this->_config->get("schemadb"));
        if (! $this->datamanager)
        {
            debug_add("order::_init_creation_mode failed to create the datamanager, see above.");
            return false;
        }
        $this->_patch_schema();
        $this->data = null;
        return true;
    }

    function _load_from_storage()
    {
        $GLOBALS["view_l10n"] = $this->_l10n;
        $this->datamanager = new midcom_helper_datamanager($this->_config->get("schemadb"));
        if (! $this->datamanager)
        {
            debug_add("order::_load_from_storage failed to create the datamanager, see above.");
            return false;
        }
        $this->_patch_schema();
        if (! $this->datamanager->init($this->storage))
        {
            debug_add("order::_load_from_storage failed to initialize the datamanager, see above.");
            return false;
        }
        $this->data =& $this->datamanager->data;
        $this->_load_orders_from_storage();
        return true;
    }

    function _refresh_datamanager() {
        $this->storage = mgd_get_event($this->storage->id);
        if (! $this->datamanager->init($this->storage))
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                                               "order::_load_from_storage Failed to refresh datamanmager, see debug log file.");
    }

    function _dm_create_callback(&$datamanager) {
        $result = Array (
            "success" => true,
            "storage" => null,
        );

        $event = mgd_get_event();
        $event->start = time();
        $event->end = $event->start;
        $event->owner = $this->_root_order_event->owner;
        $event->up = $this->_root_order_event->id;
        $event->type = $this->_ordertype_incomplete;
        $event->title = time() . "(" . getmypid() . ")";
        debug_print_r("Will create this object:", $event);
        debug_print_r("Root event is:", $this->_root_order_event);
        if ($event->up == 0) {
            die ("Event Sanity Check failed, up == 0");
        }
        $id = $event->create();
        if (! $id) {
            debug_add ("order::_dm_create_callback Could not create event: " . mgd_errstr());
            return null;
        }
        $this->storage = mgd_get_event($id);
        $this->_save_orders_to_storage();
        $result["storage"] =& $this->storage;
        return $result;
    }

    function _save_orders_to_storage() {
        debug_push_class(__CLASS__, __FUNCTION__);
        $data = Array();
        foreach ($this->_order_items as $guid => $item) {
            unset($item["product"]);
            $data[$guid] = $item;
        }
        debug_print_r("about to serialize and save this:", $data);
        $this->storage->description = serialize($data);
        if (! $this->storage->update()) {
            debug_add("storage update failed, error was: " . mgd_errstr());
            debug_pop();
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                sprintf($this->_l10n->get("could not update your order: %s"),  mgd_errstr()));
        } else {
            debug_add("storage update successful");
        }
        debug_pop();
    }

    function _load_orders_from_storage() {
        debug_push_class(__CLASS__, __FUNCTION__);
        $data = unserialize($this->storage->description);
        $this->_order_items = Array();
        foreach ($data as $guid => $data) {
            $article = mgd_get_object_by_guid($guid);
            if (   ! $article
                ||   $article->__table__ != "article"
                || ! $article->topic = $this->_topic->id)
            {
                debug_add("GUID $guid invalid for a product, could be: guid nonexistant, no article or wrong topic.",
                          MIDCOM_LOG_WARN);
                debug_print_r("Retrieved object:", $article);
                debug_print_r("Data block was:", $data);
                debug_add('Skipping entry');
                debug_pop();
                continue;
            }
            
            $pub = new net_nemein_orders_product($article);
            if (! $pub) {

                debug_add("Product {$guid} could not be loaded, this usually indicates a datamanager problem." .
                    "Last error was: {$this->_errstr}.", MIDCOM_LOG_WARN);
                debug_print_r("Retrieved object:", $article);
                debug_print_r("Data block was:", $data);
                debug_add('Skipping entry');
                debug_pop();
                continue;
            }
            
            $this->_order_items[$guid] = $data;
            $this->_order_items[$guid]["product"] = $pub;
        }
        debug_pop();
    }

}

?>