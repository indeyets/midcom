<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['hour_report'];

$view_date = strftime("%x",$view->date);
$reporter = new midcom_db_person($view->person);
$reporter_card = new org_openpsa_contactwidget($reporter);
?>
<tr<?php
if ($data['view_even'])
{
    echo " class=\"even\"";
}
?>>
    <td>&(view_date);</td>
    <td><?php echo $reporter_card->show_inline(); ?></td>
    <td>&(view.description);</td>
    <td class="hours">&(view.hours);</td>
    <td><?php
        if (!$view->is_approved)
        {
            ?>
            <input type="checkbox" name="net_nemein_hourview2_approve[<?php echo $view->id; ?>]" value="1" checked="checked" />
            <?php
        }
        else
        {
            $approver = new midcom_db_person($view->approver);
            $approver_card = new org_openpsa_contactwidget($approver);
            echo sprintf($data['l10n']->get('approved by %s on %s'), $approver_card->show_inline(), strftime("%x", $view->approved));
        }
        ?></td>
</tr>
