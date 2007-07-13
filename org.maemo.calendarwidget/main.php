<?php
/**
 * Class for rendering maemo calendar widgets
 *
 * @package org.maemo.calendarwidget 
 * @author Jerry Jalava, http://protoblogr.net
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link http://www.microformats.org/wiki/hcalendar hCalendar microformat
 */
class org_maemo_calendarwidget extends midcom_baseclasses_components_purecode
{
    /**
     * Which calendar are we showing,
     * Use constants like ORG_MAEMO_CALENDARWIDGET_MONTH
     *
     * @var int
     */
    var $type = ORG_MAEMO_CALENDARWIDGET_WEEK;

    /**
     * Length of default event slot, in seconds
     *
     * @var int
     */
    var $calendar_slot_length = 3600;

    /**
     * How wide the reservation columns should be
     * Value must be a valid CSS size option (pixels, percentage, em)
     * 
     * @var string
     */
    var $column_width = 13;

    /**
     * How high the event cells should be
     * Value must be integer
     *
     * @var int
     */
    var $cell_height = 20;

    /**
     * What unit is used on event cell heights
     * Value must be string (px or %)
     *
     * @var string
     */ 
    var $cell_height_unit = "px";
    
    /**
     * Year being currently shown
     *
     * @var int
     */
    var $year;

    /**
     * Month being currently shown
     *
     * @var int
     */
    var $month;

    /**
     * Day being currently shown
     *
     * @var int
     */
    var $day;

    /**
     * Current timestamp
     *
     * @var int
     */
    var $now;

    /**
     * Current date
     *
     * @var int
     */
    var $today;

    /**
     * Current start date
     *
     * @var int
     */
    var $from_date;

    /**
     * Current end date
     *
     * @var int
     */
    var $to_date;

    /**
     * Numerical pointer to the first day of the week
     * Defaults to Monday
     *
     * @var int
     */
    var $first_day_of_week = 1;
    
    /**
     * Abbrevation count for short week/month names
     *
     * @var int
     */
    var $abbrev = 3;

    /**
     * Hour to start the day view
     *
     * @var int
     */
    var $start_hour = 7;

    /**
     * Hour to end the day view
     *
     * @var int
     */
    var $end_hour = 18;

    /**
     * Render only hours that are visible
     *
     * @var bool
     */
    var $limit_to_visible_hours = false;

    /**
     * Calendars and events to be rendered in the calendar as PHP array
     *
     * Example:
     *
     * <code>
     * <?php
     * $this->_calendars = Array
     * (
     *     'c8b76e1e47b3427dfba717aad7a7c6a3' => Array (
     *         'name'          => 'My Calendar',
     *         'resource_type' => ORG_MAEMO_CALENDARWIDGET_RESOURCE_PERSON,
     *         'info_text'     => null,
     *         'css_class'     => 'blue',
     *         'events'  => Array (
     *             '<event GUID>' => Array (
     *                 'name'      => 'Training flight',
     *                 'location'  => 'Helsinki-Malmi airport',
     *                 'start'  => 1118005200,
     *                 'end'  => 1118005500,
     *             ),
     *         ),
     *     ),
     * );
     * ?>
     * </code>
     *
     * @var Array
     */
    var $_calendars = array();

    var $_visible_calendars = array();
    var $_visible_calendar_tags = array();

    /**
     * All events as busy items
     *
     * @var Array
     */
    var $_busy_list = array();

    /**
     * Cache of events we've shown already
     *
     * @var Array
     */
    var $_events_shown = array();

    /**
     * Cache of different timestamps used internally
     *
     * @var Array
     */
    var $_timestamp_cache = array();

    var $_calendar_layers = array();
    
    var $_jscripts = '';

    var $calendar_type_names = array();
    
    var $_slot_count = 0;
    
    /**
     * Initializes the class and sets the selected date to be shown
     *
     * @param int $year Selected year YYYY
     * @param int $month Selected month MM
     * @param int $day Selected day DD
     */
    function org_maemo_calendarwidget($year = null, $month = null, $day = null)
    {
        parent::midcom_baseclasses_components_purecode();
        
        // Default time shown is current
        if ($year)
        {
            $this->year = $year;
        }
        else
        {
            $this->year = date('Y');
        }

        if ($month)
        {
            $this->month = $month;
        }
        else
        {
            $this->month = date('m');
        }

        if ($day)
        {
            $this->day = $day;
        }
        else
        {
            $this->day = date('d');
        }

        //$this->now = mktime(0,0,0,$this->month,$this->day,$this->year);
        $this->now = time();
        $this->today = mktime(0, 0, 0, date('m',time()), date('d',time()), date('Y',time()));
        $this->_slot_count = 0;
        
        $this->weekday_names = array(
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday'
        );
        
        // Make the calendar pretty
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/org.maemo.calendarwidget/styles/calendar_table.css",
            )
        );

        // Load required Javascript files
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/org.maemo.calendarwidget/js/jquery.eventtoolbar.js');

    }
    
    function get_type_name($type)
    {
        if (!isset($this->calendar_type_names))
        {
            $this->calendar_type_names = array(
                ORG_MAEMO_CALENDARWIDGET_YEAR => 'year',
                ORG_MAEMO_CALENDARWIDGET_MONTH => 'month',
                ORG_MAEMO_CALENDARWIDGET_WEEK => 'week',
                ORG_MAEMO_CALENDARWIDGET_DAY => 'day',                      
            );          
        }

        if (!isset($this->calendar_type_names[$type]))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Unknown type: '{$type}'");
        }
        
        return $this->calendar_type_names[$type];
    }

    /**
     * Get start timestamp of the current day
     * @return integer Timestamp showing first second of the day
     */
    function get_day_start()
    {
        if (!array_key_exists('day_start', $this->_timestamp_cache))
        {
            $this->_timestamp_cache['day_start'] = mktime(0, 0, 0, $this->month, $this->day, $this->year);
        }
        return $this->_timestamp_cache['day_start'];
    }

    /**
     * Get end timestamp of the current day
     * @return integer Timestamp showing last second of the day
     */
    function get_day_end()
    {
        if (!array_key_exists('day_end', $this->_timestamp_cache))
        {
            $this->_timestamp_cache['day_end'] = mktime(23, 59, 59, $this->month, $this->day, $this->year);
        }
        return $this->_timestamp_cache['day_end'];
    }

    /**
     * Get start timestamp of the selected week. Use this to tune queries for selecting reservations
     * @param integer $timestamp Timestamp to use instead of the current date
     * @return integer Timestamp showing first second of the week
     */
    function get_week_start($timestamp = null)
    {
        if ($timestamp)
        {
            return mktime(0, 0, 0, $this->month, date('d',$timestamp) - strftime('%u', $timestamp) + 1, $this->year);
        }
        elseif (!array_key_exists('week_start', $this->_timestamp_cache))
        {
            $this->_timestamp_cache['week_start'] = mktime(0, 0, 0, $this->month, $this->day - strftime('%u', $this->get_day_start()) + 1, $this->year);
        }
        return $this->_timestamp_cache['week_start'];
    }

    /**
     * Get end timestamp of the selected week. Use this to tune queries for selecting reservations
     * @param integer $timestamp Timestamp to use instead of the current date
     * @return integer Timestamp showing last second of the week
     */
    function get_week_end($timestamp = null)
    {
        if ($timestamp)
        {
            return mktime(23, 59, 59, $this->month, strftime('%d', $this->get_week_start($timestamp)) + 6, $this->year);
        }
        if (!array_key_exists('week_end', $this->_timestamp_cache))
        {
            $this->_timestamp_cache['week_end'] = mktime(23, 59, 59, strftime('%m',$this->get_week_start()), strftime('%d',$this->get_week_start()) + 6, strftime('%Y',$this->get_week_start()));
        }
        return $this->_timestamp_cache['week_end'];
    }

    /**
     * Get start timestamp of the current month
     * @return integer Timestamp showing first second of the month
     */
    function get_month_start($year='',$month='')
    {
        $year = $this->year;
        $month = $this->month;
        
        if (!empty($year))
        {
            $year = $year;
        }
        if (!empty($month))
        {
            $month = $month;
        }
                        
        if (!array_key_exists('month_start', $this->_timestamp_cache))
        {
            $this->_timestamp_cache['month_start'] = mktime(0, 0, 0, $month, 1, $year);
        }

        return $this->_timestamp_cache['month_start'];
    }

    /**
     * Get end timestamp of the current month
     * @return integer Timestamp showing last second of the month
     */
    function get_month_end($year='',$month='')
    {
        $year = $this->year;
        $month = $this->month;
        
        if (!empty($year))
        {
            $year = $year;
        }
        if (!empty($month))
        {
            $month = $month;
        }
        
        if (!array_key_exists('month_end', $this->_timestamp_cache))
        {
            //$date = mktime(0, 0, 0, $month, 0, $year);
            //$days_in_month = date('t', $date);
            //$this->_timestamp_cache['month_end'] = mktime(0,0,0,$month,$days_in_month,$year);
            $this->_timestamp_cache['month_end'] = mktime(23, 59, 59, $month + 1, 0, $year);
        }

        return $this->_timestamp_cache['month_end'];
    }

    /**
     * Get start timestamp of the year
     * @return integer Timestamp showing first second of the year
     */
    function get_year_start($year='')
    {
        $year = $this->year;
        
        if (is_numeric($year))
        {
            $year = $year;
        }
        
        if (!array_key_exists('year_start', $this->_timestamp_cache))
        {
            $this->_timestamp_cache['year_start'] = mktime(0, 0, 0, 1, 0, $year);
        }       

        return $this->_timestamp_cache['year_start'];
    }

    /**
     * Get end timestamp of the year
     * @return integer Timestamp showing last second of the year
     */
    function get_year_end()
    {
        $year = $this->year;
        
        if (!empty($year))
        {
            $year = $year;
        }
        
        if (!array_key_exists('year_end', $this->_timestamp_cache))
        {
            $date = mktime(0, 0, 0, 12, 0, $year);
            $days_in_month = date('t', $date);
            $this->_timestamp_cache['year_end'] = mktime(23,59,59,12,$days_in_month,$year);
        }

        return $this->_timestamp_cache['year_end'];
    }

    function dates_match($timestamp1, $timestamp2)
    {
        if (   mktime(0, 0, 0, date('m',$timestamp1), date('d',$timestamp1), date('Y',$timestamp1))
            == mktime(0, 0, 0, date('m',$timestamp2), date('d',$timestamp2), date('Y',$timestamp2)) )
        {
            return true;
        }
        
        return false;
    }
    
    function first_day_of_week($first_day_of_week = null)
    {
        if ($first_day_of_week)
        {
            return $first_day_of_week;
        }
        
        return $this->first_day_of_week;
    }
    
    function last_day_of_week($first_day_of_week = null)
    {
        if (!$first_day_of_week)
        {
            $first_day_of_week = $this->first_day_of_week;
        }
        
        if ($first_day_of_week > 1)
        {
            return $first_day_of_week - 1;
        }
        
        return 7;
    }

    function date_between($date,$start,$end)
    {
        //debug_push_class(__CLASS__, __FUNCTION__);
        $date_str = strftime("%d.%m.%Y",$date);
        $start_str = strftime("%d.%m.%Y",$start);
        $end_str = strftime("%d.%m.%Y",$end);       

        $days = ($end - $start) / 86400 + 1;

        //debug_add("is {$date_str} between {$start_str} and {$end_str} ({$days} days between)?");

        $startDay = date("d", $start);
        $startMonth = date("m", $start);
        $startYear = date("Y", $start);

        for ($i=1; $i<$days; $i++)
        {
            $match = mktime(0, 0, 0, $startMonth , ($startDay+$i), $startYear);
            $match_str = strftime("%d.%m.%Y",$match);
            //debug_add("is {$date_str} == {$match_str}?");
            //if ((int)$date == (int)$match);
            if (   date("%d",$date) == date("%d",$match)
                && date("%m",$date) == date("%m",$match)
                && date("%Y",$date) == date("%Y",$match) )
            {
                //debug_add("{$date_str} ({$date}) is {$match_str} ({$match})");
                return true;
            }
        }   

        debug_pop();
        return false;
    }
        
    function has_events_on($date)
    {
        // debug_push_class(__CLASS__, __FUNCTION__);
        // $date_str = strftime("%d.%m.%Y",$date);
        // debug_add("Called for {$date_str}");
        
        foreach ($this->_busy_list as $calendar_tag => $events)
        {
            // debug_add("Checking in {$calendar_tag}");
            foreach ($events as $event_guid => $timestamps)
            {
                // debug_add("Checking event {$event_guid}");
                $event_start_date = mktime(0, 0, 0, date('m',$timestamps['start']), date('d',$timestamps['start']), date('Y',$timestamps['start']));
                $event_end_date = mktime(0, 0, 0, date('m',$timestamps['end']), date('d',$timestamps['end']), date('Y',$timestamps['end']));            

                // debug_add("event_start_date " . strftime("%d.%m.%Y",$event_start_date));
                // debug_add("event_end_date " . strftime("%d.%m.%Y",$event_end_date));
                
                if (   $event_start_date == $date
                    || $event_end_date == $date
                    || $this->date_between($date, $event_start_date, $event_end_date) )
                {
                    // debug_add("Has events");
                    return true;
                }
            }
        }

        // debug_add("Don't have events");
        // debug_pop();
        
        return false;
    }
    
    function add_calendar_layers($layer_data)
    {
        if (!is_array($layer_data))
        {
            return false;
        }
        
        $this->_calendars = $layer_data['calendars'];
        $this->_busy_list = $layer_data['busy'];
        
        return true;
    }
    
    function is_calendar_visible($calendar_tag)
    {
        if (   !isset($this->_visible_calendars[$calendar_tag])
            || !$this->_visible_calendars[$calendar_tag])
        {
            return false;
        }
        
        return true;
    }
    
    function is_calendar_tag_visible($user_tag)
    {
        if (   !isset($this->_visible_calendar_tags[$user_tag])
            || !$this->_visible_calendar_tags[$user_tag])
        {
            return false;
        }
        
        return true;
    }
    
    function hide_calendar($calendar_tag)
    {
        if (   isset($this->_visible_calendars[$calendar_tag])
            && !$this->_visible_calendars[$calendar_tag])
        {
            return false;
        }
        
        $this->_visible_calendars[$calendar_tag] = false;

        return true;
    }
    
    function hide_calendar_tag($user_tag)
    {
        if (   isset($this->_visible_calendar_tags[$user_tag])
            && !$this->_visible_calendar_tags[$user_tag])
        {
            return false;
        }
        
        if (! empty($this->_calendars[$calendar_tag]['tags']))
        {
            foreach ($this->_calendars[$calendar_tag]['tags'] as $k => $tag_data)
            {
                $this->hide_calendar_tag($tag_data['id']);
            }
        }

        $this->_visible_calendar_tags[$user_tag] = false;

        return true;
    }   

    function show_calendar($calendar_tag)
    {
        if (   isset($this->_visible_calendars[$calendar_tag])
            && $this->_visible_calendars[$calendar_tag])
        {
            return false;
        }
        
        if (! empty($this->_calendars[$calendar_tag]['tags']))
        {
            foreach ($this->_calendars[$calendar_tag]['tags'] as $k => $tag_data)
            {
                $this->show_calendar_tag($tag_data['id']);
            }
        }
        
        $this->_visible_calendars[$calendar_tag] = true;

        return true;
    }
    
    function show_calendar_tag($user_tag)
    {
        if (   isset($this->_visible_calendar_tags[$user_tag])
            && $this->_visible_calendar_tags[$user_tag])
        {
            return false;
        }
        
        $this->_visible_calendar_tags[$user_tag] = true;

        return true;
    }   
    
    function set_type($type)
    {
        $this->type = $type;
        switch ($this->type)
        {
            case ORG_MAEMO_CALENDARWIDGET_YEAR:
                $this->from_date = $this->get_year_start();
                $this->to_date = $this->get_year_end();
                break;
            case ORG_MAEMO_CALENDARWIDGET_MONTH:
                $this->from_date = $this->get_month_start();
                $this->to_date = $this->get_month_end();
                break;
            case ORG_MAEMO_CALENDARWIDGET_WEEK:
                $this->from_date = $this->get_week_start();
                $this->to_date = $this->get_week_end();
                break;
            case ORG_MAEMO_CALENDARWIDGET_DAY:
                $this->from_date = $this->get_day_start();
                $this->to_date = $this->get_day_end();
                break;
        }
    }
    
    function show($render_only=false)
    {
        $html = '';
        
        $html .= '<table width="100%" border="1" cellpadding="0" cellspacing="0" class="calendar-body">'."\n";
        
        $html .= $this->_render_header();

        switch ($this->type)
        {
            case ORG_MAEMO_CALENDARWIDGET_YEAR:
                $html .= $this->_render_year($this->year);
                break;
            case ORG_MAEMO_CALENDARWIDGET_MONTH:
                $html .= $this->_render_month($this->get_month_start(), $this->get_month_end());
                break;
            case ORG_MAEMO_CALENDARWIDGET_WEEK:
                $html .= $this->_render_week($this->get_week_start(), $this->get_week_end());
                break;
            case ORG_MAEMO_CALENDARWIDGET_DAY:
                $html .= $this->_render_day($this->get_day_start(), $this->get_day_end());
                break;
        }

        $html .= "</table>\n";
        
        $html .= '<script>';
        $html .= $this->_jscripts;
        $html .= "</script>\n\n";
        
        if ($render_only)
        {
            return $html;
        }
        
        echo $html;
    }
    
    function _render_header()
    {
        $html = '';
        
        switch ($this->type)
        {
            case ORG_MAEMO_CALENDARWIDGET_YEAR:
                $html .= $this->_render_header_year($this->year);
                break;
            case ORG_MAEMO_CALENDARWIDGET_MONTH:
                $html .= $this->_render_header_month($this->get_month_start(), $this->get_month_end());
                break;
            case ORG_MAEMO_CALENDARWIDGET_WEEK:
                $html .= $this->_render_header_week($this->get_week_start(), $this->get_week_end());
                break;
            case ORG_MAEMO_CALENDARWIDGET_DAY:
                $html .= $this->_render_header_day($this->get_day_start(), $this->get_day_end());
                break;
        }
        
        return $html;
    }
    
    function _render_header_day($start, $end)
    {
        $html = '';
        $html .= "   <thead>\n";
        $html .= "      <tr class=\"calendar-header-top\" height=\"20\">\n";        
        $html .= "         <th width=\"47\">&nbsp;</th>\n";

        $current_day = $start;
        $class_name = '';
        
        if ($this->dates_match($current_day, $this->now))
        {
            $class_name = 'today';
        }

        $day_name = date('l',$current_day);
        $day_num = date('d',$current_day);
        $day_short_name = date('D',$current_day);

        $html .= "         <th class=\"{$class_name}\" width=\"100%\" scope=\"col\" abbr=\"{$day_name}\">{$day_num}. {$day_short_name} </th>\n";
        
        $html .= "         <th width=\"14\">&nbsp;</th>\n";
        $html .= "      </tr>\n";
        $html .= "   </thead>\n";
                
        return $html;
    }

    function _render_header_week($start, $end)
    {
        $html = '';
        $html .= "   <thead>\n";
        $html .= "      <tr class=\"calendar-header-top\" height=\"20\">\n";        
        $html .= "         <th width=\"47\">&nbsp;</th>\n";

        $current_day = $start;
        $i = 7;
        $class_name = '';
        $day_name = '';
        $day_num = '';
        $day_short_name = '';
        
        // Loop through the given time range
        while ($current_day <= $end && $i)
        {
            if ($this->dates_match($current_day, $this->now))
            {
                $class_name = 'today';
            }

            $day_name = date('l',$current_day);
            $day_num = date('d',$current_day);
            $day_short_name = date('D',$current_day);                       
            $next_day = mktime(0, 0, 0, date('m',$current_day), date('d',$current_day) + 1, date('Y',$current_day));

            $html .= "         <th class=\"{$class_name}\" width=\"{$this->column_width}%\" scope=\"col\" abbr=\"{$day_name}\">{$day_num}. {$day_short_name} </th>\n";

            $current_day = $next_day;
            $class_name = '';
            $i--;
        }
        
        $html .= "         <th width=\"14\">&nbsp;</th>\n";
        $html .= "      </tr>\n";
        $html .= "   </thead>\n";
                
        return $html;
    }
    
    function _render_header_month($start, $end)
    {
        $html = '';
        $html .= "   <thead>\n";
        $html .= "      <tr class=\"calendar-header-top\" height=\"20\">\n";
        
        $current_day = $this->first_day_of_week();
        $last_weekday = $this->last_day_of_week();
        $day_name = '';
        $day_short_name = '';
        
        while ($current_day <= $last_weekday)
        {
            $day_name = $this->weekday_names[$current_day-1];
            $day_short_name = substr($this->weekday_names[$current_day-1], 0, $this->abbrev);
            $html .= "         <th width=\"{$this->column_width}%\" scope=\"col\" abbr=\"{$day_name}\"> {$day_short_name} </th>\n";
            $current_day++;
        }

        $html .= "      </tr>\n";
        $html .= "   </thead>\n";
        
        return $html;
    }
    
    function _render_header_year($year)
    {
        $html = '';
        return $html;
    }
    
    function _render_day($start, $end)
    {
        $html = '';
        
        $html .= "      <tr align=\"left\" valign=\"top\">\n";
        $html .= "         <td colspan=\"9\">\n";
        $html .= "            <div class=\"calendar-timeline-holder\">\n";
        
        $slots = $this->_get_day_slots($start);
        $html .= $this->_render_active_layers();

        $html .= "               <table width=\"100%\" border=\"1\" cellpadding=\"0\" cellspacing=\"0\" class=\"calendar-timetable\">\n";
        $html .= "                  <tbody>\n";
        
        $week_data = array();
        
        $current_day = $start;
        $is_current = false;

        for($i=0;$i<count($slots);$i++)
        {
            $hour_stamp = $slots[$i];
        
            $row_class = '';
            $hour = date('H',$hour_stamp);
            $minutes = date('i',$hour_stamp);
            $hour_index = date('G',$hour_stamp);
            $hour_string = "{$hour}:{$minutes}";
            if ($i % 2 == 0)
            {
                $row_class = ' odd';
            }
            $html .= "                     <tr class=\"calendar-timetable-row{$row_class}\">\n";
            $html .= "                        <th width=\"7%\" scope=\"row\">{$hour_string}</th>\n";
            
            $class_name = '';
            $current_day = $start;

            if ($this->dates_match($current_day, $this->today))
            {
                $class_name = 'today';
            }
    
            $html .= "                        <td width=\"100%\" class=\"{$class_name}\">&nbsp;</td>\n";
                    
            $html .= "                     </tr>\n";
        }
        $html .= "                  </tbody>\n";
        $html .= "               </table>\n";
        $html .= "            </div>\n";
        $html .= "         </td>\n";
        $html .= "      </tr>\n";
        
        return $html;
    }

    function _render_week($start, $end)
    {
        $html = '';
        
        $html .= "      <tr align=\"left\" valign=\"top\">\n";
        $html .= "         <td colspan=\"9\">\n";
        $html .= "            <div class=\"calendar-timeline-holder\">\n";
        
        $slots = $this->_get_day_slots($start);
        

        $html .= "               <table width=\"100%\" border=\"1\" cellpadding=\"0\" cellspacing=\"0\" class=\"calendar-timetable\">\n";
        $html .= "                  <tbody>\n";
        
        $week_data = array();
        
        $is_current = false;

        for($i=0;$i<count($slots);$i++)
        {
            $hour_stamp = $slots[$i];
        
            $row_class = '';
            $hour = date('H',$hour_stamp);
            $minutes = date('i',$hour_stamp);
            $hour_index = date('G',$hour_stamp);
            $hour_string = "{$hour}:{$minutes}";
            if ($i % 2 == 0)
            {
                $row_class = ' odd';
            }
            $html .= "                     <tr class=\"calendar-timetable-row{$row_class}\">\n";
            $html .= "                        <th width=\"7%\" scope=\"row\">{$hour_string}</th>\n";
            
            $class_name = '';
            $current_day = $start;
            while ($current_day <= $end)
            {
                if ($this->dates_match($current_day, $this->today))
                {
                    $class_name = 'today';
                }

                $create_date = mktime($hour, $minutes, 0, date('m',$current_day), date('d',$current_day), date('Y',$current_day));
                $next_day = mktime(0, 0, 0, date('m',$current_day), date('d',$current_day) + 1, date('Y',$current_day));

                // $onclick = '$j(\'#calendar-modal-window\').jqm({ajax: \'/event/create/' . $create_date . '\', modal: true, ';
                // $onclick .= 'onHide: function(h){h.o.remove();h.w.fadeOut(888);finishCalendarLoad(\'calendar-holder\')}, overlay: 0, ';
                // $onclick .= 'onLoad: function(h){$j.ajaxSetup({global: false})} });
                // } });';
                $onclick = "create_event('{$create_date}');";
                
                $html .= "                        <td id=\"addevent-{$create_date}\" width=\"{$this->column_width}%\" class=\"{$class_name}\" height=\"{$this->cell_height}\" onclick=\"{$onclick}\">&nbsp;</td>\n";
                //onclick=\"window.location='/event/create/{$create_date}';\"

                // $this->_jscripts .= '$j' . "('#calendar-loading').jqm({ajax: '/event/create/" . $create_date . "'});\n";

                // $this->_jscripts .= 'var jqmod = $j("#calendar-loading").jqm();';
                // $this->_jscripts .= 'console.log("jqmod: "+jqmod);';

                $class_name = '';       
                $current_day = $next_day;
            }
            
            $html .= "                     </tr>\n";
        }
        $html .= "                  </tbody>\n";
        $html .= "               </table>\n";

        $html .= $this->_render_active_layers();

        $html .= "            </div>\n";
        $html .= "         </td>\n";
        $html .= "      </tr>\n";
        
        return $html;
    }
    
    function _render_month($start, $end)
    {
        $html = '';
        
        $current_day = $this->get_week_start($start);
        $last_weekday = $this->get_week_end($end);
        $class_name = "";       
        $row_class = "";
        $day_cnt = 0;
        $row_cnt = 1;
        
        $events = $this->_get_month_events($start);
                
        $temp_days = date('t',$start);
        $offset = date('N', $start);
        if ($offset > 3 && date('m',$last_weekday) != date('m',$start))
        {
            $temp_days += $offset;
        }
        $weeks_in_month = ceil($temp_days/7);
                
        $this->cell_height = 58;

        if ($weeks_in_month > 5)
        {
            $this->cell_height = 48;
        }
        
        $html .= "<tr valign=\"top\">\n";

        while ($current_day <= $last_weekday)
        {           
            if ($day_cnt == 7)
            {
                if ($row_cnt == $weeks_in_month-1)
                {
                    $row_class = 'last-row';                    
                }
                $html .= "</tr>\n<tr valign=\"top\" class=\"{$row_class}\">\n";
                $day_cnt = 0;
                $row_cnt++;
            }
            
            $next_day = mktime(0, 0, 0, date('m',$current_day), date('d',$current_day) + 1, date('Y',$current_day));
            if ($current_day < $start || $current_day > $end)
            {
                $class_name .= " disabled-day";
            }
            if ($day_cnt == 6)
            {
                $class_name .= ' last-column';
            }

            $html .= $this->_render_month_day($current_day,$class_name,&$events);

            $class_name = "";
            $row_class = "";
            $current_day = $next_day;
            $day_cnt++;
        }

        $html .= "</tr>\n";
        
        $events = array();
        
        return $html;
    }
    
    function &_get_month_events($current_day)
    {
        // debug_push_class(__CLASS__, __FUNCTION__);
        
        $events = array();
        $added_events = array();
        $current_day_month = (int)strftime("%m", $current_day);

        // debug_add("Called for month {$current_day_month}");
        
        if (!empty($this->_calendars))
        {
            foreach($this->_calendars as $layer_tag => $layer_data)
            {
                $events[$layer_tag] = $layer_data;
                $events[$layer_tag]['events'] = array();
                
                foreach ($layer_data['events'] as $event)
                {
                    if (   $current_day_month == strftime("%m", $event->start)
                        || $current_day_month == strftime("%m", $event->end) )
                    {
                        // if (!in_array($event->guid, $added_events))
                        // {
                            // debug_print_r("Add event:",$event);
                            $events[$layer_tag]['events'][] = $event;
                            // $added_events[] = $event->guid;                          
                        // }
                    }
                }
            }           
        }
        
        // debug_print_r("Added events:",$added_events);
        // 
        // debug_pop();
        return $events;
    }   
    
    function _render_month_day($current_day,$class_name,&$events)
    {
        $html = '';
        
        $html .= "<td width=\"{$this->column_width}%\" height=\"{$this->cell_height}\" class=\"{$class_name}\">\n";
        
        if($current_day == $this->today)
        {
            $html .= "<div class=\"today\">\n";
        }
        
        $create_date = mktime(date('H',time()), 0, 0, date('m',$current_day), date('d',$current_day), date('Y',$current_day));//date('Y-m-d', $current_day);
        
        $html .= "<div>\n";
        $html .= "  <div class=\"header\">\n";
        $html .= "    <img src=\"" . MIDCOM_STATIC_URL . "/org.maemo.calendar/images/icons/new-event.png\" alt=\"New event\" width=\"12\" height=\"12\" align=\"left\"/ onclick=\"window.location='/event/create/{$create_date}';\">\n";
        $html .= "    <span class=\"day-number\">".date('d.m',$current_day)."</span>\n";                
        $html .= "  </div>\n";
        $html .= "  <div class=\"content\">";
        
        if (empty($events))
        {
            $html .= '&nbsp;';
        }
        else
        {
            foreach($events as $layer_tag => $layer_data)
            {
                $this->show_calendar($layer_tag);
                if (count($layer_data['events']) > 0)
                {
                    $html .= "   <div class=\"calendar-layer\" id=\"{$layer_tag}\">\n";
                    $html .= "      <ul>\n";
                    
                    $html .= $this->_render_month_events($current_day, $layer_tag, &$layer_data['events']);

                    $html .= "      </ul>\n";
                    $html .= "   </div>\n";                 
                }
                else
                {
                    $html .= '&nbsp;';                  
                }
            }           
        }
        
        $html .= "  </div>\n";
        $html .= "</div>\n";

        if($current_day == $this->today)
        {
            $html .= "</div>\n";
        }
        
        $html .= "</td>\n";
        
        return $html;
    }
    
    function _render_year($year)
    {
        $html = '';
        $html .= "      <tr>\n";
        $html .= "         <td>\n";
    
        for ($i=1; $i<13; $i++)
        {
            $html .= $this->_render_year_month($year, $i);
        }

        $html .= "         </td>\n";
        $html .= "      </tr>\n";
                
        return $html;
    }
    
    function _render_year_month($year, $month)
    {
        $html = '';
        
        $date = mktime(0,0,0,$month,1,$year);
        
        $current_day = $date;
        $days_in_month = date('t', $current_day);
        $month_start = $this->get_month_start($year,$month);
        $month_end = mktime(0,0,0,$month,$days_in_month,$year);
        $day_cnt = 0;
        $class_name = '';
        
        $month_name = date('F', $date);
        $html .= "<!-- {$month_name} -->\n";
        $html .= "<table width=\"100%\" border=\"1\" cellpadding=\"0\" cellspacing=\"0\" class=\"month-table\">\n";
        $html .= "   <thead>\n";
        $html .= "      <tr valign=\"top\" class=\"calendar-header-top\">\n";
        $html .= "         <th scope=\"col\" abbr=\"{$month_name}\">{$month_name}</th>\n";
        $html .= "      </tr>\n";
        $html .= "   </thead>\n";
        $html .= "   <tbody>\n";
        $html .= "     <tr>\n";
        $html .= "       <td>\n";
        $html .= "          <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"month-days\">\n";
        $html .= "           <tr>\n";
        
        while ($current_day <= $month_end)
        {
            $next_day = mktime(0, 0, 0, date('m',$current_day), date('d',$current_day) + 1, date('Y',$current_day));

            if ($day_cnt == $days_in_month)
            {
                $class_name = 'last-column';
            }
            if ($current_day == $this->today)
            {
                $class_name = 'today';
            }
            $html .= $this->_render_year_month_day($current_day,$class_name);

            $class_name = "";
            $current_day = $next_day;
            $day_cnt++;
        }

        $html .= "               </tr>\n";
        $html .= "            </table>\n";
        $html .= "         </td>\n";
        $html .= "      </tr>\n";
        $html .= "   </tbody>\n";
        $html .= "</table>\n";
        $html .= "<!-- /{$month_name} -->\n";
        
        return $html;
    }
    
    function _render_year_month_day($current_day,$class_name='')
    {
        $html = '';
        
        $has_events = $this->has_events_on($current_day);
        
        if ($has_events)
        {
            $class_name .= ' has-events';
        }
        $day_num = date('d', $current_day);
        $html .= "                  <td class=\"{$class_name}\">{$day_num}</td>\n";
        
        return $html;
    }
    
    function _render_active_layers()
    {
        // debug_push_class(__CLASS__, __FUNCTION__);
        
        $html = '';
        
        $html .= "<div class=\"calendar-layerholder\">\n";
        
        //debug_print_r('_calendars', $this->_calendars);
        
        if (empty($this->_calendars))
        {
            return;
        }
        
        foreach($this->_calendars as $layer_tag => $layer_data)
        {
            $this->show_calendar($layer_tag);
            
            $html .= "   <div class=\"calendar-layer\" id=\"calendar-layer-{$layer_tag}\">\n";

            $html .= $this->_render_events($layer_data['events'], $layer_tag);

            $html .= "   </div>\n";
        }
        
        $html .= "</div>";

        //debug_print_r('HTML: ',$html);
        
        // debug_pop();
        
        return $html;
    }
    
    function _render_events(&$events, $layer_tag)
    {
        $html = '';
        
        switch ($this->type)
        {
            case ORG_MAEMO_CALENDARWIDGET_WEEK:
                $html .= $this->_render_week_events(&$events, $layer_tag);
                break;
            case ORG_MAEMO_CALENDARWIDGET_DAY:
                $html .= $this->_render_day_events(&$events, $layer_tag);
                break;
        }

        return $html;
    }
    
    function _render_week_events(&$events, $layer_tag)
    {
        // debug_push_class(__CLASS__, __FUNCTION__);
        // $event_count = count($events);
        // debug_add("Called for #{$layer_tag} with {$event_count} events");
        
        $html = '';
        
        foreach ($events as $event)
        {
            $multiday = false;
            $start_date_string = strftime("%d.%m.%y", $event->start);
            $end_date_string = strftime("%d.%m.%y", $event->end);

            if ($start_date_string != $end_date_string)
            {
                $html .= $this->_render_multiday_event(&$event, $layer_tag);
            }
            else
            {
                $html .= $this->_render_event(&$event, $layer_tag);
            }
        }
        
        // debug_pop();     
        return $html;
    }
    
    function _render_month_events($current_day, $layer_tag, &$events)
    {
        // debug_push_class(__CLASS__, __FUNCTION__);
        // $event_count = count($events);
        // debug_add("Called for #{$layer_tag} with {$event_count} events");        

        $html = '';
        
        foreach ($events as $event)
        {
            $event_start_date = mktime(0, 0, 0, date('m',$event->start), date('d',$event->start), date('Y',$event->start));
            $event_end_date = mktime(0, 0, 0, date('m',$event->end), date('d',$event->end), date('Y',$event->end));         

            // debug_add("event_start_date " . strftime("%d.%m.%Y",$event_start_date));
            // debug_add("event_end_date " . strftime("%d.%m.%Y",$event_end_date));
            
            if (   $event_start_date == $current_day
                || $event_end_date == $current_day
                || $this->date_between($current_day, $event_start_date, $event_end_date) )
            {
                $html .= $this->_render_event(&$event, $layer_tag);
            }
        }       

        // debug_pop();     
        return $html;
    }
    
    function _render_event(&$event, $layer_tag, $override_start=false, $override_end=false, $multiday_event_id=null)
    {
        // debug_push_class(__CLASS__, __FUNCTION__);
        // debug_add("Called for {$event->title}");
        
        $html = '';
        
        $event_start = $event->start;
        $event_end = $event->end;
                
        if ($override_start)
        {
            $event_start = $override_start;         
        }
        if ($override_end)
        {
            $event_end = $override_end;         
        }

        // debug_add("Event start " . strftime("%d.%m.%y %H:%M",$event_start));
        // debug_add("Event end " . strftime("%d.%m.%y %H:%M",$event_end));
                        
        $start_time = date('H:i',$event_start);
        $end_time = date('H:i',$event_end);
        
        $toolbar_config = '';
        $bg_color = 'FFFF99';
        $event_element_id = "event-{$event->guid}";
        
        if ($multiday_event_id != null)
        {
            $event_element_id = $event_element_id . '_' . $multiday_event_id;
            $toolbar_config .= " md: true";         
        }
        
        $event_type_class = "";

        $event_tags = net_nemein_tag_handler::get_object_tags($event);
        
        $event_tag_classes = '';
        if (! empty($event_tags))
        {
            foreach ($event_tags as $tag => $data)
            {
                $event_tag_classes .= "tag-{$tag}";             
            }
        }
        
        $position = $this->_calculate_position($event_start, $event_end);           
        $height = $this->_calculate_height($event_start, $event_end);

        // debug_print_r("Event position",$position);
        // debug_add("Height {$height}");
        
        if (isset($event->bg_color))
        {
            $bg_color = $event->bg_color;
        }
        
        if (   $this->type == ORG_MAEMO_CALENDARWIDGET_WEEK
            || $this->type == ORG_MAEMO_CALENDARWIDGET_DAY )
        {
            $html .= "<div id=\"{$event_element_id}\" ";
            $html .= "title=\"{$event->title}\" class=\"calendar-object-event {$event_type_class} {$event_tag_classes}\" ";
            $html .= "style=\"height: {$height}{$this->cell_height_unit}; top: {$position['top']}px; left: {$position['left']}%; background-color: #{$bg_color};\">\n";

            $html .= "   <div class=\"calendar-object-event-header\">\n";
            $html .= "      <span class=\"event-timelabel\">{$start_time}</span>\n";
            $html .= "      <div class=\"event-toolbar-button\" title=\"" . $this->_l10n->get('event toolbox') . "\"></div>\n";
            $html .= "   </div>\n";

            $html .= "   <div class=\"calendar-object-event-content\">\n";
            $html .= "      <span class=\"event-title\">{$event->title}</span>\n";
            $html .= "      <span class=\"event-time\">{$start_time} - {$end_time}</span>\n";
            $html .= "   </div>\n";

            $html .= "</div>\n\n";
            
            $this->_jscripts .= '$j("#calendar-layer-' . $layer_tag . ' #' . $event_element_id . '").eventToolbar({' . $toolbar_config . '});'."\n";
            //$this->_jscripts .= 'console.log("layer_tag: "+$j("#' . $layer_tag . ' #' . $event_element_id . '")[0]);'."\n";
        }
        else
        {
            $html .= "<li id=\"{$event_element_id}\" class=\"{$event_type_class} {$event_tag_classes}\" style=\"background-color: #{$bg_color};\">";
            $html .= "<span class=\"event-start-time\">{$start_time}</span>";
            $html .= "<a class=\"event-title-link\" href=\"#\" title=\"{$event->title}\">{$event->title}</a>";
            $html .= "</li>\n";
        }
        
        // debug_pop();
        
        return $html;
    }
    
    function _render_multiday_event(&$event, $layer_tag)
    {
        $html = '';     
        
        $start = $event->start;
        $end = mktime(23, 59, 59, date('m',$start), date('d',$start), date('Y',$start));
        $html .= $this->_render_event($event, $layer_tag, $start, $end, 0);
        
        $current_day = mktime(0, 0, 0, date('m',$start), date('d',$start) + 1, date('Y',$start));
        
        $i = 1;
        while ($end != $event->end)
        {
            $next_start = mktime(0, 0, 0, date('m',$current_day), date('d',$current_day) + 1, date('Y',$current_day));
            $end = mktime(23, 59, 59, date('m',$current_day), date('d',$current_day), date('Y',$current_day));      
            if ($end > $event->end)
            {
                $end = $event->end;
            }
            
            $html .= $this->_render_event($event, $layer_tag, $current_day, $end, $i);
            
            $current_day = $next_start;
            $i++;
        }
        
        return $html;       
    }

    function _get_day_slots($timestamp)
    {
        // Create slots
        $slots = array();
        $slots_added = 0;
        
        $start_hour = $this->start_hour;
        $end_hour = $this->end_hour;
                        
        if(!$this->limit_to_visible_hours)
        {
            $start_hour = 0;
            $end_hour = 23;
        }

        $slot_start = mktime($start_hour, 0, 0, date('m',$timestamp), date('d',$timestamp), date('Y',$timestamp));
        $slot_end = mktime($end_hour, 59, 0, date('m',$timestamp), date('d',$timestamp), date('Y',$timestamp));
        
        $current_time = $slot_start;
        $current_hour = $start_hour;
        while ($current_time <= $slot_end)
        {
            $slots[$current_hour] = $current_time;
            $current_time = $current_time + $this->calendar_slot_length;
            $current_hour++;
        }
        
        $this->_slot_count = count($slots);
        
        return $slots;
    }
    
    function _calculate_position($start_time, $end_time, $cell_height = null)
    {
        $position = array();
        
        if (!$cell_height)
        {
            $cell_height = $this->cell_height;
        }

        //$top_multiplier = $this->_slot_count * $cell_height;
        
        $start_hour = strftime("%H", $start_time);
        $start_mins = strftime("%M", $start_time);
        $start_weekday = strftime("%u", $start_time) - 1;

        $position['left'] = 8 + ($start_weekday * $this->column_width);
        
        $multiplier = (3600 / $this->calendar_slot_length);
        $position['top'] = ($start_hour * $cell_height) * $multiplier + intval((($start_mins / 60) * $cell_height) * $multiplier);
        
        return $position;
    }

    function _calculate_height($start_time, $end_time, $cell_height = null)
    {
        if (!$cell_height)
        {
            $cell_height = $this->cell_height;
        }
        
        $max_height = $this->_slot_count * $cell_height;
        
        // Event length in minutes
        $length = ($end_time - $start_time);
        $height = ($cell_height / $this->calendar_slot_length) * $length;
        
        if ($height > $max_height)
        {
            
            return ($max_height - $length);
        }
        
        return intval($height);
    }

    function _calculate_width($start_time, $end_time, $cell_width = null)
    {
        if (!$cell_width)
        {
            $cell_width = $this->cell_width;
        }

        $length = ($end_time - $start_time);
        return ($cell_width / $this->_overlapping_reservation_count($start_time, $end_time));
    }

    function _overlapping_reservation_count($start_time, $end_time)
    {
        $count = 0;
        
        
        
        return $count;
    }
    
}