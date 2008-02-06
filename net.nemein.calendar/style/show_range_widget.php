<?php
global $net_nemein_calendar_monthSelected;
global $net_nemein_calendar_yearSelected;
global $net_nemein_calendar_showMonths;

echo "<form method=\"GET\">\n";
echo "<p>\n";
echo $GLOBALS["view_l10n"]->get("show")." ";
echo "<select name=\"net_nemein_calendar_showMonths\">\n";
$months = 0;
while ($months <= 11) {
  $selected = "";
  if ($months == $net_nemein_calendar_showMonths) {
    $selected = " selected";
  }
  echo "<option value=\"$months\"$selected>";
  echo $months+1;
  echo "</option>";
  $months++;
}
echo "</select>\n";
echo " ".$GLOBALS["view_l10n"]->get("months starting from")." ";
echo "<select name=\"net_nemein_calendar_monthSelected\">\n";
$month = 1;
while ($month < 13) {
  $selected = "";
  if ($month == $net_nemein_calendar_monthSelected) {
    $selected = " selected";
  }
  echo "<option value=\"$month\"$selected>";
  echo strftime("%B",mktime(0,0,0,$month,1,$net_nemein_calendar_yearSelected));
  echo "</option>\n";
  $month++;
}
echo "</select>\n";
echo " <select name=\"net_nemein_calendar_yearSelected\">\n";
$curyear = date('Y',time());
$year = $curyear - 1;
while ($year < $curyear + 9) {
  if ($year == $net_nemein_calendar_yearSelected) {
    $selected = " selected";
  }
  echo "<option value=\"$year\"$selected>$year</option>\n";
  $year++;
  $selected = "";
}
echo "</select>\n";
echo "<input type=\"submit\" value=\"".$GLOBALS["view_l10n"]->get("show")."\" />\n";
echo "</form>\n";
?>