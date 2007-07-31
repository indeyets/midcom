<?php

$current_date = array(  'day' => date('d'),
                        'month' => date('m'),
                        'year' => date('Y') );
$current_timezone = org_maemo_calendar_common::active_timezone();

//$timezone_abbreviations = DateTimeZone::listAbbreviations();
//$timezone_identifiers = DateTimeZone::listIdentifiers();

?>
    <div class="header" style="display: none;">
        <div class="timezone-block">
            <form id="timezone-selection-form" action="ajax/change/timezone/" method="get">
            <select name="timezone" size="1" onchange="change_timezone();">
            <?php
            echo org_maemo_calendar_common::render_timezone_list(timezone_name_get($current_timezone));
            ?>
            </select>            
            </form>
        </div>
        <div class="zoom-block">
            <img src="<?php echo MIDCOM_STATIC_URL;?>/org.maemo.calendar/images/icons/zoom-in.png" width="16" height="16" onclick="zoom_view(true,'ajax/change/view/');" />
            <img src="<?php echo MIDCOM_STATIC_URL;?>/org.maemo.calendar/images/icons/zoom-out.png" width="16" height="16" onclick="zoom_view(false,'ajax/change/view/');" />
        </div>
        <div class="date-selection-block">
            <form id="date-selection-form" action="ajax/change/date/" method="get">
            <img width="16" height="16" class="selection-previous" src="<?php echo MIDCOM_STATIC_URL;?>/org.maemo.calendar/images/icons/go-previous.png" alt="Previous" align="left" onclick="goto_prev();" />
            <img width="16" height="16" class="selection-home" src="<?php echo MIDCOM_STATIC_URL;?>/org.maemo.calendar/images/icons/go-home.png" alt="Today" align="left" onclick="goto_today();" />
            <img width="16" height="16" class="selection-next" src="<?php echo MIDCOM_STATIC_URL;?>/org.maemo.calendar/images/icons/go-next.png" alt="Next" align="left" onclick="goto_next();" />
            <select id="month-select" name="month-select" size="1" onchange="change_date();">
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
            <select id="day-select" name="day-select" size="1" onchange="change_date();">
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
            <select id="year-select" name="year-select" size="1" onchange="change_date();">
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