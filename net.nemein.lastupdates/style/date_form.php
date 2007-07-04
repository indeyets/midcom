<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<form method="get" action="&(prefix);">
    <?php echo $data['l10n']->get('list documents modified since'); ?>
    <select name="since" onchange="this.form.submit()">
        <option value=""></option>
<?php
    $stamp = net_nemein_lastupdates_viewer::last_weeks_monday();
    echo '        <option value="' . date('Y-m-d', $stamp) . '">' . $data['l10n']->get('last week') . "</option>\n";
    $stamp = mktime(0,0,1,date('n')-1, 1, date('Y'));
    echo '        <option value="' . date('Y-m-d', $stamp) . '">' . $data['l10n']->get('last month') . "</option>\n";
    $d = $data['config']->get('date_form_show_months')-1;
    $i = 1;
    while ($d--)
    {
        $i++;
        $stamp = mktime(0,0,1,date('n')-$i, 1, date('Y'));
        echo '        <option value="' . date('Y-m-d', $stamp) . '">' . strftime('%b %y', $stamp) . "</option>\n";
    }
?>
    </select>
    <input type="submit" value="<?php echo $data['l10n']->get('fetch list'); ?>" />
</form>
