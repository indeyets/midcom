<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$rule = $data['repeat_rule'];

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$form_prefix = 'net_nemein_calendar_';

// Set up the rule array
if (!is_array($rule))
{
    $rule = array();
}

// Set defaults for the form
if (!array_key_exists('interval', $rule)) 
{
    $rule['interval'] = 1;
}
if (!array_key_exists('from', $rule)) 
{
    $rule['from'] = mktime(0,0,1,date('n', $data['event']->start), date('j', $data['event']->start), date('Y', $data['event']->start));
}
if (!array_key_exists('to', $rule)) 
{
    $rule['to'] = $rule['from'];
}
if (!array_key_exists('type', $rule)) 
{
    $rule['type'] = '';
}
if (!array_key_exists('num', $rule))
{
    $rule['num'] = 0;
}
if (!array_key_exists('days', $rule))
{
    $rule['days'] = array();
}

$days = 7;
while ($days >= 0)
{
    if (!array_key_exists($days, $rule['days']))
    {
        $rule['days'][$days] = 0;
    }
    $days--;    
}
?>
<h2><?php echo sprintf($data['l10n']->get('set repeat for %s'),$data['event']->title); ?></h2>

<form method="POST" name="&(form_prefix);form" action="&(_MIDGARD['uri']);">
<div class="form_description">
  <?php echo $data['l10n']->get('repeat type'); ?>
</div>
<div class="form_field">
  <select name="&(form_prefix);Repeat_rule[type]" id="&(form_prefix);Repeat_rule_type" onChange="&(form_prefix);checkRepeatType();">
    <option value="daily"<?php if ($rule['type']=="daily") echo " selected"; ?>><?php echo $data['l10n']->get("daily"); ?></option>
    <option value="weekly_by_day"<?php if ($rule['type']=="weekly_by_day") echo " selected"; ?>><?php echo $data['l10n']->get("weekly"); ?></option>    
    <option value="monthly_by_dom"<?php if ($rule['type']=="monthly_by_dom") echo " selected"; ?>><?php echo $data['l10n']->get("monthly"); ?></option>
  </select>
</div>
<div id="&(form_prefix);Repeat_rule_days" style="display: none;">
  <div class="form_description">
    Repeat on days
  </div>
  <div class="form_field">    <div><input type="checkbox" name="&(form_prefix);Repeat_rule[days][1]" id="&(form_prefix);day_1" value="1"<?php if ($rule['days'][1]) { echo " checked=\"checked\""; } ?> /> <label for="&(form_prefix);day_1"><?php echo $data['l10n']->get("monday"); ?></label></div>    <div><input type="checkbox" name="&(form_prefix);Repeat_rule[days][2]" id="&(form_prefix);day_2" value="1"<?php if ($rule['days'][2]) { echo " checked=\"checked\""; } ?> /> <label for="&(form_prefix);day_2"><?php echo $data['l10n']->get("tuesday"); ?></label></div>    <div><input type="checkbox" name="&(form_prefix);Repeat_rule[days][3]" id="&(form_prefix);day_3" value="1"<?php if ($rule['days'][3]) { echo " checked=\"checked\""; } ?> /> <label for="&(form_prefix);day_3"><?php echo $data['l10n']->get("wednesday"); ?></label></div>    <div><input type="checkbox" name="&(form_prefix);Repeat_rule[days][4]" id="&(form_prefix);day_4" value="1"<?php if ($rule['days'][4]) { echo " checked=\"checked\""; } ?> /> <label for="&(form_prefix);day_4"><?php echo $data['l10n']->get("thursday"); ?></label></div>    <div><input type="checkbox" name="&(form_prefix);Repeat_rule[days][5]" id="&(form_prefix);day_5" value="1"<?php if ($rule['days'][5]) { echo " checked=\"checked\""; } ?> /> <label for="&(form_prefix);day_5"><?php echo $data['l10n']->get("friday"); ?></label></div>    <div><input type="checkbox" name="&(form_prefix);Repeat_rule[days][6]" id="&(form_prefix);day_6" value="1"<?php if ($rule['days'][6]) { echo " checked=\"checked\""; } ?> /> <label for="&(form_prefix);day_6"><?php echo $data['l10n']->get("saturday"); ?></label></div>    <div><input type="checkbox" name="&(form_prefix);Repeat_rule[days][0]" id="&(form_prefix);day_0" value="1"<?php if ($rule['days'][0]) { echo " checked=\"checked\""; } ?> /> <label for="&(form_prefix);day_0"><?php echo $data['l10n']->get("sunday"); ?></label></div>
  </div>
</div>
<div class="form_description">
  <?php echo $data['l10n']->get("interval"); ?>
</div>
<div class="form_field">
  <input type="text" name="&(form_prefix);Repeat_rule[interval]" size="2" value="<?php echo $rule['interval']; ?>" maxlength="2">
</div>
<div class="form_description">
  <?php echo $data['l10n']->get("from date"); ?>
</div>
<div class="form_field">
  <input class="shorttext" name="&(form_prefix);Repeat_rule[from]" maxlength="255" value="<?php echo date("Y-m-d H:i",$rule['from']); ?>">
</div>
<div class="form_description">
  <input type=radio name="&(form_prefix);Repeat_useend" value="to"<?php if ($rule['to']) echo " checked"?>> <?php echo $data['l10n']->get("until date"); ?>
</div>
<div class="form_field">
  <input class="shorttext" name="&(form_prefix);Repeat_rule[to]" maxlength="255" value="<?php echo date("Y-m-d H:i",$rule['to']); ?>">
</div>
<div class="form_description">
  <input type=radio name="&(form_prefix);Repeat_useend" value="num"<?php if ($rule['num']) echo " checked"?>> <?php echo $data['l10n']->get("repeat times"); ?>
</div>
<div class="form_field">
  <input type="text" name="&(form_prefix);Repeat_rule[num]" size="2" value="<?php echo $rule['num']; ?>" maxlength="2">
</div>
<div class="form_toolbar">
  <input type="submit" accesskey="s" name="&(form_prefix);setrepeat" value="<?php echo $data['l10n']->get("set repeat"); ?>">
  <input type="submit" name="&(form_prefix);cancel" value="<?php echo $data['l10n_midcom']->get("cancel"); ?>">
</div>
</form>
<script language="javascript">    function &(form_prefix);checkRepeatType() {        typeSel=document.getElementById('&(form_prefix);Repeat_rule_type');        daysRow=document.getElementById('&(form_prefix);Repeat_rule_days');         if (typeSel[typeSel.selectedIndex].value=='weekly_by_day') {            daysRow.style.display='block';         } else {            daysRow.style.display='none';         }    }    &(form_prefix);checkRepeatType();</script>