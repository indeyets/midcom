<?php
$rules = $data['rules'];

if (   isset($data['errors'])
    && count($data['errors']) > 0)
{
    echo "<ul class=\"errors\" style=\"color: red;\">\n";
    foreach ($data['errors'] as $error)
    {
        echo "    <li>".  $data['l10n']->get($error) ."</li>\n";
    }
    echo "</ul>\n";
}
?>
<h2><?php echo $data['l10n']->get('repeat rules'); ?></h2>
<form method="post" id="net_nemein_repeathandler_form" class="datamanager2">
    <select name="type" onchange="javascript:check_visibility(this.value);">
        <option value=""><?php echo $data['l10n']->get('choose the repeat option'); ?></option>
        <option value="daily" <?php if ($rules['type'] === 'daily') { echo 'selected="selected"'; } ?>><?php echo $data['l10n']->get('daily'); ?></option>
        <option value="weekly_by_day" <?php if ($rules['type'] === 'weekly_by_day') { echo 'selected="selected"'; } ?>><?php echo $data['l10n']->get('weekly'); ?></option>
        <option value="monthly_by_dom" <?php if ($rules['type'] === 'monthly_by_dom') { echo 'selected="selected"'; } ?>><?php echo $data['l10n']->get('monthly'); ?></option>
    </select>
    <div id="net_nemein_repeathandler_days"<?php if ($rules['type'] !== 'weekly_by_day') { echo ' style="display: none;"'; } ?>>
        <h3><?php echo $data['l10n']->get('repeat on days'); ?></h3>
<?php
for ($i = 1; $i <= 7; $i++)
{
    if ($i === 7)
    {
        $day = 0;
    }
    else
    {
        $day = $i;
    }
    
    if (   isset($rules['days'][$day])
        && $rules['days'][$day] == 1)
    {
        $checked = ' checked="checked"';
    }
    else
    {
        $checked = '';
    }
?>
        <label for="net_nemein_repeathandler_day_&(i);">
            <input id="net_nemein_repeathandler_day_&(i);" type="checkbox" name="days[]" value="&(day);"&(checked:h); />
            <?php echo strftime('%A', mktime(0, 0, 0, 4, 8 + $i, 2007)); ?>
        </label>
<?php
//    echo "            <input type=\"checkbox\"{$checked} name=\"days[]\" value=\"{$i}\" />".  ."\n";
}
?>
    </div>
    <label for="net_nemein_repeathandler_repeat_interval">
        <span class="field_text">
            <?php echo $data['l10n']->get('repeat interval'); ?>
        </span>
    </label>
    <input type="text" class="shorttext" id="net_nemein_repeathandler_repeat_interval" name="interval" value="<?php echo $rules['interval']; ?>" />
    <label for="net_nemein_repeathandler_repeat_from">
        <span class="field_text">
            <?php echo $data['l10n']->get('beginning from'); ?>
        </span>
    </label>
    <input type="text" id="net_nemein_repeathandler_repeat_from" name="from" value="<?php echo ($rules['from']) ? $rules['from'] : date('Y-m-d'); ?>" />
    <label for="net_nemein_repeathandler_repeat_to_date">
        <span class="field_text">
            <?php echo $data['l10n']->get('until date'); ?>
        </span>
    </label>
    <input type="radio" id="net_nemein_repeathandler_repeat_to_date" onchange="javascript:check_radiobox(this.id);" name="end_switch" value="date"<?php if ($rules['end_type'] !== 'num') { echo ' checked="checked"';} ?> />
    <input type="text" id="net_nemein_repeathandler_repeat_to_date_field" name="to" value="<?php echo ($rules['to']) ? $rules['to'] : date('Y-m-d'); ?>"<?php if ($rules['end_type'] === 'num') { echo ' readonly="readonly"';} ?> />
    
    <label for="net_nemein_repeathandler_repeat_num">
        <span class="field_text">
            <?php echo $data['l10n']->get('times'); ?>
        </span>
    </label>
    <input type="radio" id="net_nemein_repeathandler_repeat_num" onchange="javascript:check_radiobox(this.id);" name="end_switch" value="num"<?php if ($rules['end_type'] === 'num') { echo ' checked="checked"';} ?> />
    <input type="text" id="net_nemein_repeathandler_repeat_num_field" name="num" value="&(rules['num']:);" <?php if ($rules['end_type'] !== 'num') { echo ' readonly="readonly"';} ?> />
    
    <br /><br />
    <div class="form_toolbar">
        <input class="save" type="submit" name="f_submit" value="<?php echo $data['l10n_midcom']->get('save'); ?>" />
        <input class="cancel" type="submit" name="f_cancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
    </div>
</form>
