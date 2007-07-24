<?php
/**
 * Class for rendering maemo calendar panels buddylist accordion leaf
 *
 * @package org.maemo.calendarpanel 
 * @author Jerry Jalava, http://protoblogr.net
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link http://www.microformats.org/wiki/hcalendar hCalendar microformat
 */
class org_maemo_calendarpanel_buddylist_leaf extends midcom_baseclasses_components_purecode
{
    var $name;
    var $title;
    
    var $_buddies = array();
    
    /**
     * Initializes the class
     *
     */
    function org_maemo_calendarpanel_buddylist_leaf()
    {
        parent::midcom_baseclasses_components_purecode();
        
        $this->name = 'buddylist';
        $this->title = $this->_l10n->get($this->name);
    }
    
    function add_buddies($buddies)
    {
        if (empty($buddies))
        {
            return;
        }
        
        foreach ($buddies as $person_id => $person)
        {
            $this->_add_buddy($person);
        }
    }
    
    function add_penging_buddies($pending_buddies)
    {
        if (empty($pending_buddies))
        {
            return;
        }
        
        foreach ($pending_buddies as $person_id => $person)
        {
            $this->_add_pending_buddy($person);
        }
    }    
    
    function generate_content()
    {
        $html = "";

        $html .= $this->_render_menu();
        $html .= $this->_render_buddylist();
        $html .= $this->_render_pending_list();
                
        return $html;
    }
    
    function refresh_buddylist_items()
    {
        $html = '';
        $buddies = array();
        
        $user = $_MIDCOM->auth->user->get_storage();
        $qb = net_nehmer_buddylist_entry::new_query_builder();
        $qb->add_constraint('account', '=', $user->guid);
        $qb->add_constraint('blacklisted', '=', false);
        $buddies_qb = $qb->execute();

        foreach ($buddies_qb as $buddy)
        {
            $person = new midcom_db_person($buddy->buddy);
            if ($person)
            {
                $buddies[] = $person;
            }
        }

        foreach ($buddies as $k => $person)
        {
            $html .= org_maemo_calendarpanel_buddylist_leaf::render_buddylist_item($person);            
        }
        
        return $html;
    }
    
    function _render_menu()
    {
        $html = "";
        
        $html .= "<div class=\"accordion-leaf-menu\">\n";
        $html .= "   <ul class=\"leaf-menu\">\n";

        $html .= "      <li><a href=\"#\" onclick=\"load_modal_window('/ajax/buddylist/search');\" title=\"Add buddy\"><img src=\"" . MIDCOM_STATIC_URL . "/org.maemo.calendarpanel/images/icons/contact-new.png\" alt=\"Add buddy\" /></a></li>\n";

        $html .= "   </ul>\n";
        $html .= "</div>\n";
        
        return $html;
    }

    function _render_buddylist()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $html = '';
        
        if (empty($this->_buddies))
        {
            return $html;
        }
        
        $html .= "<div class=\"buddylist\">\n";
        $html .= "   <ul id=\"buddylist-item-list\">\n";
        
        foreach ($this->_buddies as $k => $person)
        {
            $html .= org_maemo_calendarpanel_buddylist_leaf::render_buddylist_item($person);            
        }

        $html .= "   </ul>\n";      
        $html .= "</div>\n";
        
        debug_pop();
        
        return $html;
    }
    
    function _render_pending_list()
    {
        debug_push_class(__CLASS__, __FUNCTION__);        

        $html = "<div class=\"pending-title\">Pending requests</div>\n";
        
        if (empty($this->_pending_buddies))
        {
            $html .= "<span class=\"empty-pending-list\">No requests pending.</span>";
            return $html;
        }
        
        $html .= "<div class=\"pending-list\">\n";
        $html .= "   <ul id=\"pending-item-list\">\n";
        
        foreach ($this->_pending_buddies as $k => $person)
        {
            $html .= $this->_render_pending_item($person);            
        }
        
        $html .= "   </ul>\n";      
        $html .= "</div>\n";        

        debug_pop();
        
        return $html;
    }
    
    function render_buddylist_item(&$person)
    {
        $html = '';
        
        $user = $_MIDCOM->auth->user->get_storage();
        $qb = net_nehmer_buddylist_entry::new_query_builder();
        $qb->add_constraint('account', '=', $user->guid);
        $qb->add_constraint('buddy', '=', $person->guid);
        $buddy_qb = $qb->execute();
        
        if (count($buddy_qb) == 0)
        {
            return $html;
        }
        
        $buddy = $buddy_qb[0];
        
        $buddy_status = 'approved';
        if (! $buddy->isapproved)
        {
            $buddy_status = 'not-approved';
        }
        
        $html .= "<li id=\"buddylist-item-{$person->guid}\" class=\"{$buddy_status}\">\n";
        $html .= "   <div class=\"buddy-details\">\n";
        
        $html .= "      <span class=\"vcard\" title=\"\">\n";
        $html .= "         <span class=\"uid\" style=\"display: none;\">{$person->guid}</span>\n";
        $html .= "         <span class=\"n\">\n";
        $html .= "            <span class=\"given-name\">{$person->firstname}</span> <span class=\"family-name\">{$person->lastname}</span>\n";
        $html .= "         </span>\n";
        
        $html .= "      </span>\n";
        
        $html .= "   </div>\n";     
        $html .= "   <div class=\"buddy-online-status\">\n";
        
        if ($buddy->isapproved)
        {
            $online_class = 'offline';
            $buddy_online_status = org_maemo_calendarpanel_buddylist_leaf::get_online_status($person);
            if ($buddy_online_status['is_online'])
            {
                $online_class = 'online';
            }

            $html .= "      <span class=\"status-{$online_class}\" title=\"{$buddy_online_status['status_string']}\">&nbsp;</span>\n";            
        }
        
        $html .= "   </div>\n";
        $html .= "   <div class=\"buddy-actions\">\n";
        $html .= "<img src=\"" . MIDCOM_STATIC_URL . "/org.maemo.calendarpanel/images/icons/icon-properties.png\" alt=\"Properties\" width=\"16\" height=\"16\" />";
        $delete_action = "remove_person_from_buddylist('{$person->guid}');";
        $html .= "<img src=\"" . MIDCOM_STATIC_URL . "/org.maemo.calendarpanel/images/icons/trash.png\" alt=\"Delete\" width=\"16\" height=\"16\" onclick=\"{$delete_action}\"/>";        
        $html .= "   </div>\n";     
        $html .= "</li>\n";
                
        return $html;
    }
    
    function _render_pending_item(&$person)
    {
        $html = '';
        
        $html .= "<li id=\"pending-list-item-{$person->guid}\">\n";
        $html .= "   <div class=\"buddy-details\">\n";
        
        $html .= "      <span class=\"vcard\" title=\"\">\n";
        $html .= "         <span class=\"uid\" style=\"display: none;\">{$person->guid}</span>\n";
        $html .= "         <span class=\"n\">\n";
        $html .= "            <span class=\"given-name\">{$person->firstname}</span> <span class=\"family-name\">{$person->lastname}</span>\n";
        $html .= "         </span>\n";
        
        $html .= "      </span>\n";
        
        $html .= "   </div>\n";
        $html .= "   <div class=\"buddy-actions\">\n";
        $approve_action = "approve_buddy_request('{$person->guid}');";
        $html .= "<img src=\"" . MIDCOM_STATIC_URL . "/org.maemo.calendarpanel/images/icons/approve-buddy.png\" alt=\"Properties\" width=\"16\" height=\"16\" onclick=\"{$approve_action}\" />";
        $deny_action = "deny_buddy_request('{$person->guid}');";
        $html .= "<img src=\"" . MIDCOM_STATIC_URL . "/org.maemo.calendarpanel/images/icons/buddy-deny.png\" alt=\"Delete\" width=\"16\" height=\"16\" onclick=\"{$deny_action}\" />";        
        $html .= "   </div>\n";     
        $html .= "</li>\n";
                
        return $html;
    }    
    
    function get_online_status(&$person)
    {
        $statuses = array(  'midcom' => false,
                            'skype' => false,
                            'jabber' => false,
                            'is_online' => false,
                            'status_string' => 'Offline' );
        
        return $statuses;
    }
    
    function _add_buddy(&$object)
    {
        $this->_buddies[] = $object;
    }
    
    function _add_pending_buddy(&$object)
    {
        $this->_pending_buddies[] = $object;
    }    
    
    function _get_available_buddies()
    {
        return $this->_buddies;
    }
}