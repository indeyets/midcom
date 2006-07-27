<?php
/**
 * @package net.nemein.orders
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Orders Order Factory class.
 * 
 * @package net.nemein.orders
 */
class net_nemein_orders_order_factory extends net_nemein_orders__base {
    
    var $_sort_order;
    
    function net_nemein_orders_order_factory() {
        
        /* Initialize base class */
        parent::net_nemein_orders__base();
        
        $this->_sort_order = "start";
        
    }
    
    function set_sort_order($order) {
        $this->_sort_order = $order;
    }
    
    function get_sort_order() {
        return $this->_sort_order;
    }
    
    function list_pending() {
        $events = mgd_list_events($this->_root_order_event->id, $this->_sort_order, $this->_ordertype_pending);
        if (! $events) {
            if (mgd_errstr() == 'MGD_ERR_OK')
            {
                // nothing wrong but an empty resultset.
                return Array();
            }
            debug_add("Could not list pending orders: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            return false;
        }
        return $this->_fetch_to_array($events);
    }
    
    function list_pending_between($start, $end) {
        $events = mgd_list_events_between($this->_root_order_event->id, $start, $end, 
                                          $this->_sort_order, $this->_ordertype_pending);
        if (! $events) {
            if (mgd_errstr() == 'MGD_ERR_OK')
            {
                // nothing wrong but an empty resultset.
                return Array();
            }
            debug_add("Could not list pending orders: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            return false;
        }
        return $this->_fetch_to_array($events);
    }
    
    function list_delivered() {
        $events = mgd_list_events($this->_root_order_event->id, $this->_sort_order, $this->_ordertype_delivered);
        if (! $events) {
            if (mgd_errstr() == 'MGD_ERR_OK')
            {
                // nothing wrong but an empty resultset.
                return Array();
            }
            debug_add("Could not list delivered orders: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            return false;
        }
        return $this->_fetch_to_array($events);
    }
    
    function list_delivered_between($start, $end) {
        $events = mgd_list_events_between($this->_root_order_event->id, $start, $end, 
                                          $this->_sort_order, $this->_ordertype_delivered);
        if (! $events) {
            if (mgd_errstr() == 'MGD_ERR_OK')
            {
                // nothing wrong but an empty resultset.
                return Array();
            }
            debug_add("Could not list delivered orders: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            return false;
        }
        return $this->_fetch_to_array($events);
    }
    
    /* Not implemented, deletion has the same effect.
    function list_cancelled() {
        $events = mgd_list_events($this->_root_order_event->id, $this->_sort_order, $this->_ordertype_cancelled);
        if (! $events) {
            debug_add("Could not list cancelled orders: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            return false;
        }
        return $this->_fetch_to_array($events);
    }
    
    function list_cancelled_between($start, $end) {
        $events = mgd_list_events_between($this->_root_order_event->id, $start, $end, 
                                          $this->_sort_order, $this->_ordertype_cancelled);
        if (! $events) {
            debug_add("Could not list cancelled orders: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            return false;
        }
        return $this->_fetch_to_array($events);
    }
    */
    
    function list_incomplete() {
        $events = mgd_list_events($this->_root_order_event->id, $this->_sort_order, $this->_ordertype_incomplete);
        if (! $events) {
            if (mgd_errstr() == 'MGD_ERR_OK')
            {
                // nothing wrong but an empty resultset.
                return Array();
            }
            debug_add("Could not list incomplete orders: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            return false;
        }
        return $this->_fetch_to_array($events);
    }
    
    function list_incomplete_between($start, $end) {
        $events = mgd_list_events_between($this->_root_order_event->id, $start, $end, 
                                          $this->_sort_order, $this->_ordertype_incomplete);
        if (! $events) {
            if (mgd_errstr() == 'MGD_ERR_OK')
            {
                // nothing wrong but an empty resultset.
                return Array();
            }
            debug_add("Could not list incomplete orders: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            return false;
        }
        return $this->_fetch_to_array($events);
    }
    
    function list_corrupt() {
        $events = mgd_list_events($this->_root_order_event->id, $this->_sort_order, $this->_ordertype_corrupt);
        if (! $events) {
            if (mgd_errstr() == 'MGD_ERR_OK')
            {
                // nothing wrong but an empty resultset.
                return Array();
            }
            debug_add("Could not list corrupt orders: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            return false;
        }
        return $this->_fetch_to_array($events);
    }
        
    
    /*** Internal Helpers ***/
    
    function _fetch_to_array($fetchable) 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $result = Array();
        
        if ($this->_config->get('enable_payment_management') && $this->_auth->is_mailing_company())
        {
            debug_add('We have to skip unpaid orders.');
            $skip_unpaid = true;
        }
        else
        {
            $skip_unpaid = false;
        }
        
        while ($fetchable->fetch()) 
        {
            $event = mgd_get_event($fetchable->id);
            $order = new net_nemein_orders_order($event);
            if (! $order) 
            {
                debug_add("Failed to build an order object out of the event $event->id, skipping.", MIDCOM_LOG_WARN);
                debug_print_r("Event object was:", $event);
                continue;
            }
            if ($skip_unpaid && ! $order->data['paid'])
            {
                debug_add("The order {$order->data['_storage_id']} has not yet been paid, skipping it.");
                continue;
            }
            $result[$event->guid()] = $order;
        }
        
        debug_pop();
        return $result;
    }
    
}

?>