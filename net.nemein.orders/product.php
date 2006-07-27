<?php
/**
 * @package net.nemein.orders
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Orders Product class.
 * 
 * @package net.nemein.orders
 */
class net_nemein_orders_product extends net_nemein_orders__base {
    
    var $datamanager;
    var $data;
    var $storage;
    
    function net_nemein_orders_product($article = null) {
        
        /* Initialize base class */
        parent::net_nemein_orders__base();
        
        /* Initialize ourselves */
        if (is_null($article)) { 
            $this->storage = null;
            if (! $this->_init_creation_mode()) {
                $this = false;
                return false;
            }
        } else {
            $this->storage = $article;
            if (! $this->_load_from_storage()) {
                $this = false;
                return false;
            }
        }
        
    }
    
    function init_create() {
        if (! $this->datamanager->init_creation_mode($this->_config->get("schema_product"), $this)) {
            debug_add("product::init_create failed to prepare the creation datamanager, see above.");
            $this->_errstr = "Failed to initialize the datamanager's creation mode.";
            return false;
        } else {
            return true;
        }
    }
    
    function update_index()
    {
        $indexer =& $GLOBALS['midcom']->get_service('indexer');
        $indexer->index($this->datamanager);
    }
    
    /**************** Midgard IO Overloads **************/
    
    function delete() {
        if (is_null($this->storage)) {
            debug_add("Can't delete, we don't have an object to. Failing silently.");
            return;
        }
        $guid = $this->storage->guid();
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
        $indexer =& $GLOBALS['midcom']->get_service('indexer');
        $indexer->delete($guid);
        
	    // Invalidate the cache modules
	    $GLOBALS['midcom']->cache->invalidate($guid);
    }
    
    /**************** Storage quantity interface **************/
    
    function add_to_storage ($quantity) {
        $this->_check_storage();
        
        $instock = $this->data["instock"] + $quantity;
        $available = $this->data["available"] + $quantity;
        
        /*
         * No error handling right now, the parameter call doesn't really do that
         * (even thought it is documented)
         *
        if (   !$this->storage->parameter("midcom.helper.datamanager", "data_instock", $instock)
            || !$this->storage->parameter("midcom.helper.datamanager", "data_available", $available))
        {
            debug_add("Could not update storage parameters, last error was: " . mgd_errstr());
            return false;
        }
         */
        
        $this->storage->parameter("midcom.helper.datamanager", "data_instock", $instock);
        $this->storage->parameter("midcom.helper.datamanager", "data_available", $available);
        
        $this->_refresh_datamanager();
        $this->update_index();
        return true;
    }
    
    function reserve_items ($quantity) {
        $this->_check_storage();
        if ($quantity > 0 && $quantity > $this->data["available"]) {
            $this->_errstr = sprintf($this->_l10n->get("not enough items available, %i requested, %i available."),
                                     $quantity, $this->data["available"]);
            return false;
        }
        
        $available = $this->data["available"] - $quantity;
        
        if (!$this->storage->parameter("midcom.helper.datamanager", "data_available", $available))
        {
            debug_add("Could not update at least one of the storage parameters, last error was: " 
                      . mgd_errstr());
            $this->_errstr = sprintf($this->_l10n->get("could not update storage parameters: %s"), 
                                     mgd_errstr());
            return false;
        }
        
        $this->_refresh_datamanager();
        $this->update_index();
        return true;
    }
    
    function cancel_reserved_items ($quantity) {
        return $this->reserve_items(-1 * $quantity);
    }
    
    function deliver_items ($quantity) {
        $this->_check_storage();
        
        if ($quantity > 0 && $quantity > $this->data["instock"]) {
            $this->_errstr = sprintf($this->_l10n->get("not enough items in stock, %i requested, %i available."),
                                     $quantity, $this->data["instock"]);
            return false;
        }
        
        $instock = $this->data["instock"] - $quantity;
        
        if (!$this->storage->parameter("midcom.helper.datamanager", "data_instock", $instock))
        {
            debug_add("Could not update at least one of the storage parameters, last error was: " 
                      . mgd_errstr());
            $this->_errstr = sprintf($this->_l10n->get("could not update storage parameters: %s"), 
                                     mgd_errstr());
            return false;
        }
        
        $this->_refresh_datamanager();
        $this->update_index();
        return true;
    }
    
    function undeliver_items ($quantity) {
        $this->_check_storage();
        
        $instock = $this->data["instock"] + $quantity;
        
        if (!$this->storage->parameter("midcom.helper.datamanager", "data_instock", $instock))
        {

            debug_add("Could not update at least one of the storage parameters, last error was: " 
                      . mgd_errstr());
            $this->_errstr = sprintf($this->_l10n->get("could not update storage parameters: %s"), 
                                     mgd_errstr());
            return false;
        }
        
        $this->_refresh_datamanager();
        $this->update_index();
        return true;
    }
    
    /**************** Misc Queries *****************/
    
    function get_remaining_free_items($theorder) {
        /* Scan all orders for the same product, name and email address, and calculate 
         * the remaining free items
         */
        
        $factory = new net_nemein_orders_order_factory();
        $pending = $factory->list_pending();
        $delivered = $factory->list_delivered();
        $orders = array_merge($delivered, $pending);
        
        $free = $this->data["maxfreecopies"];
        $ourguid = $this->storage->guid();
        
        foreach ($orders as $guid => $order) {
            if ($order->storage->id == $theorder->storage->id)
                continue;
            
            if (   ! array_key_exists('name', $theorder->data)
                || ! array_key_exists('email', $theorder->data)
                || ! array_key_exists('name', $order->data)
                || ! array_key_exists('email', $order->data))
                continue;
            
            if (   $order->data["name"] != $theorder->data["name"]
                || $order->data["email"] != $theorder->data["email"])
                continue;
            
            $items = $order->get_order();
            if (! array_key_exists($ourguid, $items))
                continue;
            
            $free -= $items[$ourguid]["quantity"];
        }
        
        return max($free, 0);
    }
    
    
    /**************** Internal Helper Functions ***************/
    
    function _check_storage() {
        if (is_null($this->storage))
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, 
                                               "Tried to access a nonexisting storage object.");
    }
    
    function _init_creation_mode() {
        $GLOBALS["view_l10n"] = $this->_l10n;
        $this->datamanager = new midcom_helper_datamanager($this->_config->get("schemadb"));
        if (! $this->datamanager) {
            debug_add("product::_init_creation_mode failed to create the datamanager, see above.");
            $this->_errstr = "Failed to create a datamanager.";
            return false;
        }
        $this->data = null;
        return true;
    }
    
    function _load_from_storage() {
        $GLOBALS["view_l10n"] = $this->_l10n;
        $this->datamanager = new midcom_helper_datamanager($this->_config->get("schemadb"));
        if (! $this->datamanager) {
            debug_add("product::_load_from_storage failed to create the datamanager, see above.");
            $this->_errstr = "Failed to create a datamanager instance.";
            return false;
        }
        if (! $this->datamanager->init($this->storage)) {
            debug_add("product::_load_from_storage failed to initialize the datamanager, see above.");
            $this->_errstr = "Failed to initialize datamanager.";
            return false;
        }
        $this->data =& $this->datamanager->data;
        return true;
    }
    

    function _refresh_datamanager() {
        $this->storage = mgd_get_article($this->storage->id);
        if (! $this->datamanager->init($this->storage))
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                                               "product::_dm_create_callback Failed to refresh datamanmager, see debug log file.");
    }
    
    function _dm_create_callback(&$datamanager) {
        $result = Array (
            "success" => true,
            "storage" => null,
        );
        
        $midgard = $GLOBALS["midcom"]->get_midgard();
        $this->storage = mgd_get_article();
        $this->storage->topic = $this->_topic->id;
        $this->storage->author = $midgard->user;
        $id = $this->storage->create();
        if (! $id) {
            debug_add ("product::_dm_create_callback Could not create article: " . mgd_errstr());
            return null;
        }
        $this->storage = mgd_get_article($id);
        $result["storage"] =& $this->storage;
        return $result;
    }
}

?>