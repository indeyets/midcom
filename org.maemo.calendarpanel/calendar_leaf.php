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
class org_maemo_calendarpanel_calendar_leaf extends midcom_baseclasses_components_purecode
{
	var $name;
	var $title;
	
	var $_calendars = array();
	
	var $calendarwidget = null;
	
    /**
     * Initializes the class
     *
     */
	function org_maemo_calendarpanel_calendar_leaf(&$calendarwidget)
    {
		parent::midcom_baseclasses_components_purecode();
		
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
	
	function generate_content()
	{
		$html = "";
		
		$html .= $this->_render_calendar_list();
		
		return $html;
	}
	
	function _get_available_calendars()
	{
		return;
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
			
			$visible = $this->calendarwidget->is_calendar_visible($calendar_tag);
			$visibility = 'checked="checked"';
			if (!$visible)
			{
				$visibility = '';
			}
			
			$bg_color = 'FFFF99';
			
			$html .= "   <li class=\"{$item_class}\" id=\"calendar-list-item-{$calendar_tag}\">\n";
			$html .= "      <div class=\"calendar-visibility\"><input type=\"checkbox\" name=\"\" value=\"\" {$visibility}/></div>\n";
			$html .= "      <div class=\"calendar-name\" style=\"background-color: #{$bg_color};\" onclick=\"toggle_tag_listing('{$calendar_tag}');\">{$calendar_data['name']}</div>\n";
			$html .= "      <div class=\"calendar-order\">\n";
			$html .= "         <a class=\"graph-arrowUp\"></a>\n";
			$html .= "         <a class=\"graph-arrowDown\"></a>\n";
			$html .= "      </div>\n";
			$html .= "      <div class=\"calendar-actions\"><img src=\"" . MIDCOM_STATIC_URL . "/org.maemo.calendarpanel/images/icons/icon-properties.png\" alt=\"Properties\" width=\"16\" height=\"16\" /></div>\n";
			$html .= "   </li>\n";
			
			if (! empty($calendar_data['tags']))
			{
				$html .= "   <div class=\"calendar-tag-list\" id=\"calendar-list-item-{$calendar_tag}-tags\">\n";
				$html .= "      <ul>\n";
				foreach ($calendar_data['tags'] as $k => $tag_data)
				{
					$visible = $this->calendarwidget->is_calendar_tag_visible($tag_data['id']);
					$visibility = 'checked="checked"';
					if (!$visible)
					{
						$visibility = '';
					}
					
					$bg_color = 'FFFF99';
					if ($tag_data['color'])
					{
						$bg_color = $tag_data['color'];
					}
					
					$html .= "         <li>\n";
					$html .= "            <div class=\"calendar-visibility\"><input type=\"checkbox\" name=\"\" value=\"\" {$visibility}/></div>\n";
					$html .= "            <div class=\"calendar-name\" style=\"background-color: #{$bg_color};\">{$tag_data['name']}</div>\n";
					$html .= "            <div class=\"calendar-actions\"><img src=\"" . MIDCOM_STATIC_URL . "/org.maemo.calendarpanel/images/icons/icon-properties.png\" alt=\"Properties\" width=\"16\" height=\"16\" /></div>\n";
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