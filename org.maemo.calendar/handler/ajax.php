<?php
/**
 * @package org.maemo.calendar
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is an AJAX handler class for org.maemo.calendar
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 * 
 * @package org.maemo.calendar
 */

require('index.php');

class org_maemo_calendar_handler_ajax extends org_maemo_calendar_handler_index 
{

	var $_selected_time = null;
	var $_calendar_type = ORG_MAEMO_CALENDARWIDGET_WEEK;
	
    /**
     * Simple default constructor.
     */
    function org_maemo_calendar_handler_ajax()
    {
        parent::org_maemo_calendar_handler_index();

		$this->_selected_time = time();

        // Always run in uncached mode
        $_MIDCOM->cache->content->no_cache();
    }
    
    /**
     * _on_initialize is called by midcom on creation of the handler. 
     */
    function _on_initialize()
    {
		$this->current_user = $_MIDCOM->auth->user->get_storage();
		$this->layer_data = array( 'calendars' => array(), 'busy' => array() );
    }
    
    /**
     * The handler for changing the current date
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     * 
     */
    function _handler_ajax_change_date($handler_id, $args, &$data)
	{
		debug_push_class(__CLASS__, __FUNCTION__);
		//debug_add(sprintf('ajax_change_date got args "%s"', var_dump($args)));
		
		$_MIDCOM->skip_page_style = true;
		
		if (count($args) > 1)
		{
			$this->_selected_time = $args[0];
			$this->_calendar_type = $args[1];
		}
		else
		{
			$this->_selected_time = $args[0];			
		}

		// debug_add(sprintf('_handler_ajax_change_date got _selected_time "%s"', $this->_selected_time));
		// debug_add(sprintf('which makes "%s"', date('d.m.Y',$this->_selected_time)));
		
		$this->_request_data['maemo_calender'] = new org_maemo_calendarwidget(date('Y', $this->_selected_time), date('m', $this->_selected_time), date('d', $this->_selected_time));
		$this->_request_data['maemo_calender']->set_type($this->_calendar_type);
		
		$this->_request_data['maemo_calender']->start_hour = $this->_config->get('day_start_hour');
		$this->_request_data['maemo_calender']->end_hour = $this->_config->get('day_end_hour');
				
		switch ($this->_calendar_type)
		{
			case ORG_MAEMO_CALENDARWIDGET_WEEK:
				$this->_request_data['maemo_calender']->calendar_slot_length = $this->_config->get('week_slot_length') * 60;
			break;
			case ORG_MAEMO_CALENDARWIDGET_MONTH:
				$this->_request_data['maemo_calender']->calendar_slot_length = $this->_config->get('month_slot_length') * 60;
				$this->_request_data['maemo_calender']->column_width = $this->_config->get('month_column_width');
			break;
		}
		
		$this->_fetch_calendars();
		$this->_request_data['maemo_calender']->add_calendar_layers($this->layer_data);
		
		debug_pop();
		
        return true;
    }
    
    /**
     * The handler for changing the current view
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     * 
     */
    function _handler_ajax_change_view($handler_id, $args, &$data)
	{
		debug_push_class(__CLASS__, __FUNCTION__);
		
		$_MIDCOM->skip_page_style = true;
		
		if (count($args) == 0)
		{
			return false;
		}
		
		$this->_selected_time = $args[0];
		$this->_calendar_type = $args[1];
		
		// debug_add(sprintf('_handler_ajax_change_view got _selected_time "%s"', $this->_selected_time));
		
		$this->_request_data['maemo_calender'] = new org_maemo_calendarwidget(date('Y', $this->_selected_time), date('m', $this->_selected_time), date('d', $this->_selected_time));
		$this->_request_data['maemo_calender']->set_type($this->_calendar_type);
		
		$this->_request_data['maemo_calender']->start_hour = $this->_config->get('day_start_hour');
		$this->_request_data['maemo_calender']->end_hour = $this->_config->get('day_end_hour');
				
		switch ($this->_calendar_type)
		{
			case ORG_MAEMO_CALENDARWIDGET_WEEK:
				$this->_request_data['maemo_calender']->cell_height = $this->_config->get('week_row_height');
				$this->_request_data['maemo_calender']->cell_height_unit = $this->_config->get('week_row_unit');
				$this->_request_data['maemo_calender']->calendar_slot_length = $this->_config->get('week_slot_length') * 60;
				//print_r($this->_request_data['maemo_calender']);
			break;
			case ORG_MAEMO_CALENDARWIDGET_MONTH:
				$this->_request_data['maemo_calender']->calendar_slot_length = $this->_config->get('month_slot_length') * 60;
				$this->_request_data['maemo_calender']->column_width = $this->_config->get('month_column_width');
			break;
		}

		$this->_fetch_calendars();
		$this->_request_data['maemo_calender']->add_calendar_layers($this->layer_data);
		
		debug_pop();
		
        return true;
    }

	function _show_ajax_change_date($handler_id, &$data)
	{
		$this->_update_scroll_top();
		$this->_request_data['maemo_calender']->show();
	}

	function _show_ajax_change_view($handler_id, &$data)
	{
		$this->_update_scroll_top();
		$this->_request_data['maemo_calender']->show();
	}
	
	function _update_scroll_top()
	{
		if ($this->_calendar_type == ORG_MAEMO_CALENDARWIDGET_WEEK)
		{
			$slh = 3600 / $this->_request_data['maemo_calender']->calendar_slot_length;
	        $scrollTop = $this->_request_data['maemo_calender']->cell_height * ($this->_request_data['maemo_calender']->start_hour * $slh);
			echo "<script type=\"text/javascript\">\n";
			echo "  calendar_config['start_hour_x'] = {$scrollTop};\n";
			echo "</script>\n";			
		}
	}
    
}
?>
