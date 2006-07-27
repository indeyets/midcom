<?php

class net_nemein_reservations__base {
    
    var $_debug_prefix;
    
    var $_prefix;
    var $_config;
    var $_config_dm;
    var $_l10n;
    var $_l10n_midcom;
    var $_topic;
    var $_errstr;
    var $_resource;
    var $_reservation;
    
    var $_root_group;
    var $_root_event;
    var $_auth;
    
    var $_publication;
    var $_order;
    
    
    function net_nemein_reservations__base () {
        
        $this->_debug_prefix = get_class($this) . "::" ;
        
        $this->_topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
        $this->_config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
        $this->_config_dm =& $GLOBALS["midcom"]->get_custom_context_data("configuration_dm");
        $this->_l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");
        $this->_l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");
        $this->_errstr =& $GLOBALS["midcom"]->get_custom_context_data("errstr");
        $this->_root_event =& $GLOBALS["midcom"]->get_custom_context_data("root_event");
        $this->_root_group =& $GLOBALS["midcom"]->get_custom_context_data("root_group");
        $this->_auth =& $GLOBALS["midcom"]->get_custom_context_data("auth");
        $this->_resource =& $GLOBALS["midcom"]->get_custom_context_data("resource");
        $this->_reservation =& $GLOBALS["midcom"]->get_custom_context_data("reservation");
        
    }
    
    
}

?>