<?php

$current_date = array(	'day' => date('d'),
						'month' => date('m'),
						'year' => date('Y') );
$current_timezone = timezone_open('Europe/Helsinki');

//$timezone_abbreviations = DateTimeZone::listAbbreviations();
$timezone_identifiers = DateTimeZone::listIdentifiers();

?>
	<div class="header">
		<div class="timezone-block">
			<form id="timezone-selection-form" action="ajax/change/timezone/" method="GET">
			<select name="timezone" size="1">
				<option value="0">Select Timezone</option>
				<?php
				for($i=0;$i<count($timezone_identifiers);$i++)
				{
					$selected = "";
					if($timezone_identifiers[$i] == timezone_name_get($current_timezone))
					{
						$selected = ' selected="selected"';
					}
					//echo '<option value="'.$timezone_identifiers[$i].'" '.$selected.'>'.$timezone_identifiers[$i].'</option>';
				}
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