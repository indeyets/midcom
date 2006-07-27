<?php

class net_nemein_reservations_auth {
    
    var $user;
    var $_midgard;
    var $_poweruser;
    
    function net_nemein_reservations_auth($topic = null) {
        $this->_midgard = mgd_get_midgard();
        
        $this->user = mgd_get_person($this->_midgard->user);
        if ($this->user === false) 
        {
            debug_add ("User in \$midgard was not found: " . mgd_errstr());
            $this->user = null;
            $this->_poweruser = false;
        } 
        else 
        {
            $this->_poweruser = $this->user->parameter("Interface","Power_User") != "NO" ? true : false;
        }
    }
    
    function is_admin() {
        return ($this->_midgard->admin == true);
    }
    
    function is_poweruser() {
        if ($this->is_admin())
        {
            return true;
        }
        
        if (! $this->is_owner())
        {
            return false;
        }
        
        return $this->_poweruser;
    }
    
    function is_owner($topic = null) {
        if ($this->is_admin())
        {
            return true;
        }
        
        if (is_null($topic))
        {
            return mgd_is_topic_owner($GLOBALS['midcom']->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC));
        }
        else
        {
            return mgd_is_topic_owner($topic);
        }
    }
    
    function check_is_owner() {
        $this->_check($this->is_owner(), "auth: need to be owner");
    }
    
    function check_is_poweruser() {
        $this->_check($this->is_owner(), "auth: need to be poweruser");
    }
    
    function check_is_admin() {
        $this->_check($this->is_owner(), "auth: need to be admin");
    }
    
    function _check($ok, $msg) {
        if (! $ok) 
        {
            $GLOBALS["midcom"]->generate_error($this->_l10n->get($msg), MIDCOM_ERRFORBIDDEN);
        }
    }
}

?>