<?php
/**
 * Class for rendering maemo calendar panels calendar accordion leaf
 *
 * @package org.maemo.calendarpanel 
 * @author Jerry Jalava, http://protoblogr.net
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link http://www.microformats.org/wiki/hcalendar hCalendar microformat
 */
class org_maemo_calendarpanel_calendar_leaf extends org_maemo_calendarpanel_leaf
{    
    var $_calendars = array();
    
    var $calendarwidget = null;
    
    /**
     * Initializes the class
     *
     */
    function org_maemo_calendarpanel_calendar_leaf(&$calendarwidget)
    {
        parent::org_maemo_calendarpanel_leaf();
        
        $this->name = 'calendar';
        $this->title = $this->_l10n->get($this->name);
        $this->calendarwidget = $calendarwidget;
    }
    
    function add_calendars(&$calendars)
    {
        if (empty($calendars))
        {
            return;
        }
        
        $this->_calendars = $calendars;
    }
    
    function _get_available_calendars()
    {
        return $this->_calendars;
    }

    function generate_content()
    {
        $html = "";

        $html .= $this->_render_menu();
        $html .= $this->_render_calendar_list();
        
        return $html;
    }

    function _render_menu()
    {
        $html = "";
        
        $html .= "<div class=\"accordion-leaf-menu\">\n";
        $html .= "   <ul class=\"leaf-menu\">\n";

        $html .= "      <li><img src=\"" . MIDCOM_STATIC_URL . "/org.maemo.calendarpanel/images/icons/new-tag.png\" alt=\"New tag\" onclick=\"load_modal_window('midcom-exec-org.maemo.calendar/layers.php?action=show_create_tag&layer_id={$_MIDCOM->auth->user->guid}');\" /></li>\n";
        
        $html .= "   </ul>\n";
        $html .= "</div>\n";
        
        return $html;
    }
    
    function _render_calendar_list()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $html = '';
        
        // debug_print_r("this->_calendars:",$this->_calendars);
        
        if (empty($this->_calendars))
        {
            return $html;
        }
        
        $html .= "<ul>\n";
        
        $i = 1;
        $total_calendars = count($this->_calendars);
        foreach ($this->_calendars as $calendar_tag => $calendar_data)
        {
            $item_class = '';
            if ($i == $total_calendars)
            {
                $item_class = 'last-item';
            }
            if ($i == 1)
            {
                $item_class = 'first-item';
            }
            if ($total_calendars == 1)
            {
                $tag_item_class = 'only-item';
            }           
            if ($i % 2 == 0)
            {
                $item_class .= ' odd';
            }   
            
            $visible = $this->calendarwidget->is_calendar_visible($calendar_tag);
            $visibility = 'checked="checked"';
            if (!$visible)
            {
                $visibility = '';
            }
            
            $bg_color = 'FFFF99';
            if (! empty($calendar_data['color']))
            {
                $bg_color = $calendar_data['color'];
            }
            
            $html .= "   <li class=\"{$item_class}\" id=\"calendar-list-item-{$calendar_tag}\">\n";
            $html .= "      <div class=\"calendar-visibility\"><input type=\"checkbox\" name=\"\" value=\"\" {$visibility}/ onclick=\"toggle_layer_visibility('{$calendar_tag}');\"></div>\n";
            $html .= "      <div class=\"calendar-name\" style=\"background-color: #{$bg_color};\" onclick=\"toggle_tag_listing('{$calendar_tag}');\">{$calendar_data['name']}</div>\n";
            $html .= "      <div class=\"calendar-order\">\n";
            // $html .= "         <a class=\"graph-arrowUp\"></a>\n";
            // $html .= "         <a class=\"graph-arrowDown\"></a>\n";
            $html .= "      </div>\n";
            
            $properties_action = '';
            if ($_MIDCOM->auth->user->guid == $calendar_tag)
            {
                $properties_action = "edit_calendar_layer_properties('{$calendar_tag}');";                
            $html .= "      <div class=\"calendar-actions\"><img src=\"" . MIDCOM_STATIC_URL . "/org.maemo.calendarpanel/images/icons/icon-properties.png\" alt=\"Properties\" width=\"16\" height=\"16\" onclick=\"{$properties_action}\" /></div>\n";
            }
                        
            $html .= "   </li>\n";
            
            if (   !empty($calendar_data['tags'])
                && $_MIDCOM->auth->user->guid == $calendar_tag)
            {
                $html .= "   <div class=\"calendar-tag-list\" id=\"calendar-list-item-{$calendar_tag}-tags\">\n";
                $html .= "      <ul>\n";

                $total_tags = count($calendar_data['tags']);
                foreach ($calendar_data['tags'] as $k => $tag_data)
                {
                    $visible = $this->calendarwidget->is_calendar_tag_visible($tag_data['id']);
                    $visibility = 'checked="checked"';
                    if (!$visible)
                    {
                        $visibility = '';
                    }
                    
                    $tag_item_class = '';
                    if ($k == $total_tags-1)
                    {
                        $tag_item_class = 'last-item';
                    }
                    if ($k == 0)
                    {
                        $tag_item_class = 'first-item';
                    }
                    if ($total_tags == 1)
                    {
                        $tag_item_class = 'only-item';
                    }
                    if ($k % 2 == 0)
                    {
                        $tag_item_class .= ' odd';
                    }                   
                    
                    $bg_color = 'FFFF99';
                    if ($tag_data['color'])
                    {
                        $bg_color = $tag_data['color'];
                    }
                    
                    $html .= "         <li class=\"{$tag_item_class}\" id=\"calendar-list-item-{$tag_data['id']}\">\n";
                    $html .= "            <div class=\"calendar-visibility\"><input type=\"checkbox\" name=\"\" value=\"\" {$visibility}/ onclick=\"toggle_tag_visibility('{$calendar_tag}', '{$tag_data['id']}');\"></div>\n";
                    $html .= "            <div class=\"calendar-name\" style=\"background-color: #{$bg_color};\">{$tag_data['name']}</div>\n";
                    $html .= "            <div class=\"calendar-order\">\n";
                    // $html .= "               <a class=\"graph-arrowUp\"></a>\n";
                    // $html .= "               <a class=\"graph-arrowDown\"></a>\n";
                    $html .= "            </div>\n";

                    $properties_action = '';
                    if ($_MIDCOM->auth->user->guid == $calendar_tag)
                    {
                        $properties_action = "edit_calendar_layer_tag_properties('{$calendar_tag}', '{$tag_data['id']}');";
                    $html .= "            <div class=\"calendar-actions\"><img src=\"" . MIDCOM_STATIC_URL . "/org.maemo.calendarpanel/images/icons/icon-properties.png\" alt=\"Properties\" width=\"16\" height=\"16\" onclick=\"{$properties_action}\" /></div>\n";
                    }
                    
                    $html .= "         </li>\n";
                }
                $html .= "      </ul>\n";
                $html .= "   </div>\n";
            }
            
            $i++;
        }

        $html .= "</ul>\n";
        
        debug_pop();
        return $html;
    }
}