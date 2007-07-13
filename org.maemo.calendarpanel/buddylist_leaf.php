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
    
    function generate_content()
    {
        $html = "";
        $html .= $this->_render_buddylist();
        
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
        $html .= "   <ul>\n";
        
        foreach ($this->_buddies as $k => $person)
        {
            $html .= $this->_render_buddylist_item($person);            
        }

        $html .= "   </ul>\n";      
        $html .= "</div>\n";
        
        debug_pop();
        
        return $html;
    }
    
    function _render_buddylist_item(&$person)
    {
        $html = '';
        
        $html .= "<li id=\"buddylist-item-{$person->guid}\">\n";
        $html .= "   <div class=\"buddy-details\">\n";
        
        $html .= "      <span class=\"vcard\" title=\"\">\n";
        $html .= "         <span class=\"uid\" style=\"display: none;\">{$person->guid}</span>\n";
        $html .= "         <span class=\"n\">\n";
        $html .= "            <span class=\"given-name\">{$person->firstname}</span> <span class=\"family-name\">{$person->lastname}</span>\n";
        $html .= "         </span>\n";
        
        $html .= "      </span>\n";
        
        $html .= "   </div>\n";     
        $html .= "   <div class=\"buddy-online-status\">\n";
        
        $online_class = 'offline';
        $buddy_online_status = $this->_get_online_status($person);
        if ($buddy_online_status['is_online'])
        {
            $online_class = 'online';
        }
        
        $html .= "      <span class=\"status-{$online_class}\" title=\"{$buddy_online_status['status_string']}\">&nbsp;</span>\n";
        $html .= "   </div>\n";
        $html .= "   <div class=\"buddy-actions\">\n";
        $html .= "<img src=\"" . MIDCOM_STATIC_URL . "/org.maemo.calendarpanel/images/icons/icon-properties.png\" alt=\"Properties\" width=\"16\" height=\"16\" />";
        $html .= "   </div>\n";     
        $html .= "</li>\n";
                
        return $html;
    }
    
    function _get_online_status(&$person)
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
    
    function _get_available_buddies()
    {
        return $this->_buddies;
    }
}