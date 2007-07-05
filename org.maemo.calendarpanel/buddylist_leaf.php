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
	
	var $_buddies;
	
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
	
	function add_calendars($calendars)
	{
		if (empty($calendars))
		{
			return;
		}
		
		foreach ($calendars as $calendar)
		{
			$this->_add_calendar($calendar);
		}
	}
	
	function generate_content()
	{
		$html = "";
		$html .= 'Buddylist leaf content'."\n";
		
		return $html;
	}
	
	function _add_buddy(&$object)
	{
		$_buddies[] = $object;
	}
	
	function _get_available_buddies()
	{
		return;
	}
}