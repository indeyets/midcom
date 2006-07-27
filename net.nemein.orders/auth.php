<?php
/**
 * @package net.nemein.orders
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Orders Authentication and Permission helper class.
 * 
 * @package net.nemein.orders
 */
class net_nemein_orders_auth extends net_nemein_orders__base {
    
    var $user;
    var $_midgard;
    var $_poweruser;
    
    function net_nemein_orders_auth() {
        /* Initialize Base class */
        parent::net_nemein_orders__base();    
        
        $this->_midgard = mgd_get_midgard();
        
        $this->user = mgd_get_person($this->_midgard->user);
        if ($this->user === false) {
            debug_add ("User in \$midgard was not found: " . mgd_errstr());
            $this->user = null;
            $this->_poweruser = false;
        } else {
            $this->_poweruser = $this->user->parameter("Interface","Power_User") != "NO" ? true : false;
        }
    }
    
    function is_mailing_company() {
        if (is_null($this->_mailing_company_group) || is_null($this->user))
        {
            return false;
        }
        
        return mgd_is_member($this->_mailing_company_group->id, $this->user->id);
    }
    
    function is_admin() {
        return ($this->_midgard->admin == true);
    }
    
    function is_poweruser() {
        return (   $this->is_admin() 
                || ($this->is_owner() && $this->_poweruser));
    }
    
    function is_owner() {
        return (   $this->is_admin() 
                || mgd_is_topic_owner($this->_topic->id));
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
    
    function check_is_mailing_company() {
        $this->_check($this->is_mailing_company(), "auth: need to be mailing company");
    }
    
    function check_is_not_mailing_company() {
        $this->_check(! $this->is_mailing_company(), "auth: need not to be mailing company");
    }
    
    function _check($ok, $msg) {
        if (! $ok) {
            $GLOBALS["midcom"]->generate_error($this->_l10n->get($msg), MIDCOM_ERRFORBIDDEN);
        }
    }
}

?>