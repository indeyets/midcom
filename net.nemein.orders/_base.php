<?php
/**
 * @package net.nemein.orders
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Orders Utility base class.
 * 
 * @package net.nemein.orders
 */
class net_nemein_orders__base {
    
    var $_config;
    var $_config_dm;
    var $_l10n;
    var $_l10n_midcom;
    var $_topic;
    var $_errstr;
    var $_root_order_event;
    var $_mailing_company_group;
    var $_auth;
    
    var $_product;
    var $_order;
    
    var $_ordertype_pending;
    var $_ordertype_delivered;
    /* var $_ordertype_cancelled; ** NOT IMPLEMENTED ** */
    var $_ordertype_incomplete;
    var $_ordertype_corrupt;
    
    
    function net_nemein_orders__base () {
        
        $this->_topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
        $this->_config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
        $this->_config_dm =& $GLOBALS["midcom"]->get_custom_context_data("configuration_dm");
        $this->_l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");
        $this->_l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");
        $this->_errstr =& $GLOBALS["midcom"]->get_custom_context_data("errstr");
        $this->_root_order_event =& $GLOBALS["midcom"]->get_custom_context_data("root_order_event");
        $this->_mailing_company_group =& $GLOBALS["midcom"]->get_custom_context_data("mailing_company_group");
        $this->_auth =& $GLOBALS["midcom"]->get_custom_context_data("auth");
        $this->_product =& $GLOBALS["midcom"]->get_custom_context_data("product");
        $this->_order =& $GLOBALS["midcom"]->get_custom_context_data("order");
        
        /* some "constants" */
        $this->_ordertype_pending = 0;
        $this->_ordertype_delivered = 1;
        /* $this->_ordertype_cancelled = 2; ** NOT IMPLEMENTED ** */
        $this->_ordertype_incomplete = 3;
        $this->_ordertype_corrupt = 4;
    }
    
    
}

?>