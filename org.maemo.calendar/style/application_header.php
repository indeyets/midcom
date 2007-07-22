<?php

$current_date = array(  'day' => date('d'),
                        'month' => date('m'),
                        'year' => date('Y') );
$current_timezone = org_maemo_calendar_common::active_timezone();

//$timezone_abbreviations = DateTimeZone::listAbbreviations();
//$timezone_identifiers = DateTimeZone::listIdentifiers();

?>
    <div class="header">
        <div class="timezone-block">
            <form id="timezone-selection-form" action="ajax/change/timezone/" method="GET">
            <select name="timezone" size="1" onchange="change_timezone();">
                <option value="0">Select Timezone</option>
            <?php
            function render_timezone_list($selected_zone) {
                $structure = '';
                $timezone_identifiers = timezone_identifiers_list();
                $i = 0;
                foreach ($timezone_identifiers as $zone) {
                    $zone = explode('/',$zone);
                    if (isset($zone[1]))
                    {
                        $zones[$i]['continent'] = $zone[0];
                        $zones[$i]['city'] = $zone[1];
                        $i++;                        
                    }
                }
                asort($zones);
                foreach ($zones as $zone) {
                    extract($zone);
                    if (   $continent == 'Africa'
                        || $continent == 'America'
                        || $continent == 'Antarctica'
                        || $continent == 'Arctic'
                        || $continent == 'Asia'
                        || $continent == 'Atlantic'
                        || $continent == 'Australia'
                        || $continent == 'Europe'
                        || $continent == 'Indian'
                        || $continent == 'Pacific')
                    {
                        if (! isset($current_continent))
                        {
                            $structure .= "<optgroup label=\"{$continent}\">\n"; // continent                            
                        }
                        elseif ($current_continent != $continent)
                        {
                            $structure .= "</optgroup>\n<optgroup label=\"{$continent}\">\n"; // continent
                        }
                        
                        $selected = "";
                        if ($city != '')
                        {
                            $value = "{$continent}/{$city}";
                            if ($value == $selected_zone)
                            {
                                $selected = "selected=\"selected\" ";
                            }
                            $structure .= "<option {$selected} value=\"{$value}\">" . str_replace('_',' ',$city) . "</option>\n"; //Timezone
                        }
                        else
                        {
                            if ($continent == $selected_zone)
                            {
                                $selected = "selected=\"selected\" ";
                            }
                            $structure .= "<option {$selected} value=\"{$continent}\">{$continent}</option>\n"; //Timezone                            
                        }
                        
                        $current_continent = $continent;
                    }
                }
                $structure .= "</optgroup>\n";
                return $structure;
            }
            echo render_timezone_list(timezone_name_get($current_timezone));
            ?>
            </select>            
            </form>
        </div>
        <div class="zoom-block">
            <img src="<?php echo MIDCOM_STATIC_URL;?>/org.maemo.calendar/images/icons/zoom-in.png" width="16" height="16" onclick="zoom_view(true,'/ajax/change/view/');" />
            <img src="<?php echo MIDCOM_STATIC_URL;?>/org.maemo.calendar/images/icons/zoom-out.png" width="16" height="16" onclick="zoom_view(false,'/ajax/change/view/');" />
        </div>
        <div class="date-selection-block">
            <form id="date-selection-form" action="ajax/change/date/" method="GET">
            <img class="selection-previous" src="<?php echo MIDCOM_STATIC_URL;?>/org.maemo.calendar/images/icons/go-previous.png" alt="Previous week" align="left" />
            <img class="selection-next" src="<?php echo MIDCOM_STATIC_URL;?>/org.maemo.calendar/images/icons/go-next.png" alt="Next week" align="right" />
            <select name="month-select" size="1" onchange="change_date();">
                <?php
                $month = 1;
                while($month < 13)
                {
                    if(strlen($month) < 2)
                    {
                        $month = "0$month";
                    }
                    $selected = '';
                    if($current_date['month'] == $month)
                    {
                        $selected = ' selected="selected"';
                    }
                    echo '<option value="'.$month.'"'.$selected.'>'.$month.'</option>';
                    $month++;
                }
                ?>
            </select>
            /
            <select name="day-select" size="1" onchange="change_date();">
                <?php
                $day = 1;
                $max_days = date("t", mktime(0, 0, 0, $current_date['month'], 1, $current_date['year']));
                while($day <= $max_days)
                {                   
                    if(strlen($day) < 2)
                    {
                        $day = "0$day";
                    }
                    $selected = '';
                    if($current_date['day'] == $day)
                    {
                        $selected = ' selected="selected"';
                    }
                    echo '<option value="'.$day.'"'.$selected.'>'.$day.'</option>';
                    $day++;
                }
                ?>
            </select>
            /       
            <select name="year-select" size="1" onchange="change_date();">
                <?php
                $curyear = date('Y',time());
                $year = $curyear - 2;
                while ($year < $curyear + 9) {
                  if ($year == $current_date['year']) {
                    $selected = " selected";
                  }
                  echo "<option value=\"$year\"$selected>$year</option>\n";
                  $year++;
                  $selected = "";
                }
                ?>
            </select>
            </form>
        </div>
    </div>