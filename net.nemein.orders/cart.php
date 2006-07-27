<?php
/**
 * @package net.nemein.orders
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Orders Shopping Cart class.
 * 
 * @package net.nemein.orders
 */
class net_nemein_orders_cart extends net_nemein_orders__base {
    
    var $_session;
    var $_items;
    
    /**
     * The name of the session key we store the shopping cart in. This
     * distinguishes multiple n.n.orders installations on the same site.
     * Basis is the configuration topic (which might be symlinked).
     * 
     * @var string
     * @access private
     */
    var $_cart_name;
    
    function net_nemein_orders_cart () {
        
        /* Initialize base class */
        parent::net_nemein_orders__base();
        
        $this->_cart_name = "cart_items_";
        $config_topic = $this->_config->get('symlink_config_topic');
        if (is_null($config_topic))
        {
            $this->_cart_name .= $this->_topic->id;
        }
        else
        {
            $this->_cart_name .= $config_topic->id;
        }
        
        /* Initialize local data store, fire up sessioning */
        $this->_session = new midcom_service_session();
        if ($this->_session->exists($this->_cart_name)) {
            $this->_items = $this->_session->get($this->_cart_name);
        } else {
            $this->_items = Array();
        }
        
    }
    
    /* Basic Shopping Cart Interface */
    
    function add_item ($guid, $quantity) {
        if (! is_numeric($quantity)) {
            debug_add("Argument quantity [$quantity] is not numeric", MIDCOM_LOG_ERROR);
            return false;
        }
        
        if (array_key_exists($guid, $this->_items)) {
            $this->_items[$guid] += $quantity;
            debug_add("shopping_cart: Increased quantity of $guid by $quantity to " . $this->_items[$guid]);
        } else {
            $this->_items[$guid] = $quantity;
            debug_add("shopping_cart: Added item $guid with quantity $quantity.");
        }
        $this->_check($guid);
        
        $this->_session->set($this->_cart_name, $this->_items);
        return true;
    }
    
    function remove_item ($guid) {
        if (array_key_exists($guid, $this->_items)) {
            unset ($this->_items[$guid]);
            debug_add("shopping_cart: Removed item $guid from the cart.");
            $this->_session->set($this->_cart_name, $this->_items);
            return true;
        } else {
            debug_add("shopping_cart: Tried to remove non-existant item $guid from the cart.", 
                      MICDOM_LOG_INFO);
            return false;
        }
    }
    
    function remove_all () {
        debug_add("Clearing shopping cart.");
        $this->_items = Array();
        $this->_session->remove($this->_cart_name);
        return true;
    }
    
    function set_item ($guid, $quantity) {
        if (! is_numeric($quantity)) {
            debug_add("Argument quantity [$quantity] is not numeric", MIDCOM_LOG_ERROR);
            return false;
        }
        
        $this->_items[$guid] = $quantity;
        $this->_check($guid);
        
        $this->_session->set($this->_cart_name, $this->_items);
        return true;
    }
    
    function _check ($guid) {
        /* Checks for the various limits (available count, max per order count */
        $data = mgd_get_object_by_guid($guid);
        if ($data === false) 
        {
            debug_add("The cart item guid $guid is invalid.", MIDCOM_LOG_ERROR);
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                "Could not create publicaiton, see the debug logs for details.");
            // This will exit  
        }
        $pub = new net_nemein_orders_product($data);
        if ($pub === false) 
        {
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                "Could not create publicaiton, see the debug logs for details.");
            // This will exit
        }
        $reduced = false;
        
        if ($pub->data['maxperorder'] > 0)
        {
	        $max = min((integer) $pub->data["maxperorder"], (integer) $pub->data["available"]);
	        
	        if ($this->_items[$guid] > $max) 
	        {
	            $this->_items[$guid] = $max;
	            $this->_session->set("processing_msg", sprintf($this->_l10n->get("Not enough copies available, reduced to %d"), $max));
	            debug_add("Copy amount for $guid removed to $max, maxperorder is {$pub->data['maxperorder']}"
	                . ", available is {$pub->data['available']}");
	        }
        }
        if ($this->_items[$guid] <= 0)
        {
            debug_add("The product {$guid} has no items available or someone tried to order zero or negative items counts, removing it from the cart.");
            unset ($this->_items[$guid]);
        }
    }
    
    function get_cart () {
        $result = Array();
        foreach ($this->_items as $guid => $quantity) {
            $data = Array();
            $data["guid"] = $guid;
            $data["quantity"] = $quantity;
            $data["object"] = mgd_get_object_by_guid($guid);
            if ($data["object"] === false) 
            {
                debug_add("The cart item guid $guid is invalid, skipping.", MIDCOM_LOG_ERROR);
                continue;
            }
            $data["product"] = new net_nemein_orders_product($data["object"]);
            if ($data["product"] === false) 
            {
                $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                    "Could not create publicaiton, see the debug logs for details.");
            }
            $data["netamount"] = $data["quantity"] * $data["product"]->data["price"];
            $data["amount"] = round($data["netamount"] * (1 + ($data["product"]->data["vat"] / 100)), 2);
            $result[$guid] = $data;
        }
        return $result;
    }
}

?>