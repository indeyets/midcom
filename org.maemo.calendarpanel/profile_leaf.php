<?php
/**
 * Class for rendering maemo calendar panels profile accordion leaf
 *
 * @package org.maemo.calendarpanel 
 * @author Jerry Jalava, http://protoblogr.net
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link http://www.microformats.org/wiki/hcalendar hCalendar microformat
 */
class org_maemo_calendarpanel_profile_leaf extends midcom_baseclasses_components_purecode
{
    var $name;
    var $title;
    
    var $_schemadb = null;
    var $_schema = null;
    var $_controller = null;

    var $_person = null;
    
    /**
     * Initializes the class
     *
     */
    function org_maemo_calendarpanel_profile_leaf()
    {
        parent::midcom_baseclasses_components_purecode();
        
        $this->name = 'profile';
        $this->title = $this->_l10n->get($this->name);
    }
    
    function set_schemadb(&$schemadb, $schema)
    {
        $this->_schemadb =& $schemadb;
        $this->_schema = $schema;
    }
    
    function set_person(&$person)
    {
        if (   !is_object($person)
            || is_null($this->_schemadb)
            || is_null($this->_schema) )
        {
            return false;
        }

        $this->_set_person(&$person);
        
        return true;
    }
    
    function generate_content()
    {
        $html = "";

        $html .= $this->_render_menu();
        $html .= $this->_render_basic_info();
        $html .= $this->_render_tags();
                        
        return $html;
    }

    function _render_menu()
    {
        $html = "";
        
        $html .= "<div class=\"accordion-leaf-menu\">\n";
        $html .= "   <ul class=\"leaf-menu\">\n";

        $html .= "      <li><a href=\"#\" onclick=\"load_modal_window('ajax/profile/view/{$this->_person->guid}');\" title=\"View full profile\"><img src=\"" . MIDCOM_STATIC_URL . "/org.maemo.calendar/images/icon-properties.png\" alt=\"View full profile\" /></a></li>\n";

        $html .= "      <li><a href=\"#\" onclick=\"load_modal_window('ajax/profile/edit/{$this->_person->guid}');\" title=\"Edit profile\"><img src=\"" . MIDCOM_STATIC_URL . "/org.maemo.calendarpanel/images/icons/edit.png\" alt=\"Edit profile\" /></a></li>\n";

        $html .= "   </ul>\n";
        $html .= "</div>\n";
        
        return $html;
    }
    
    function _render_basic_info()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $html = "";
        
        $user_timezone = org_maemo_calendar_common::get_user_timezone();
        $identifier = timezone_name_get($user_timezone);
        
        $html .= "   <div class=\"profile-info\">\n";
        
        $html .= "      <span class=\"vcard\" title=\"\">\n";
        $html .= "         <span class=\"uid\" style=\"display: none;\">{$this->_person->guid}</span>\n";
        $html .= "         <span class=\"n\">\n";
        $html .= "            <span class=\"given-name\">{$this->_person->firstname}</span> <span class=\"family-name\">{$this->_person->lastname}</span>\n";
        $html .= "         </span>\n";
        $html .= "         <span class=\"email\">{$this->_person->email}</span>\n";
        $html .= "         <span class=\"url\">{$this->_person->homepage}</span>\n";
        $html .= "         <span class=\"tz\">{$identifier}</span>\n";
        $html .= "      </span>\n";
        
        $html .= "   </div>\n";        
        
        debug_pop();
        
        return $html;
    }
    
    function _render_tags()
    {
        $html = '';
        
        return $html;
    }
    
    function _set_person(&$person)
    {
        $this->_person = $person;
        
        $this->_controller =& new midcom_helper_datamanager2_datamanager($this->_schemadb);
        if (   ! $this->_controller
            || ! $this->_controller->set_schema($this->_schema) )
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a DM2 instance.');
            // This will exit.
        }

        $this->_controller->set_storage($this->_person);
    }
}

?>