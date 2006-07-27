<?php
global $view;
$view_date = strftime("%x",$view->date);
$reporter = new midgard_person();
$reporter->get_by_id($view->person);
?>
<tr<?php
if ($GLOBALS["view_even"])
{
    echo " class=\"even\"";
}
?>>
    <td>&(view_date);</td>
    <td>&(reporter.lastname); &(reporter.firstname);</td>
    <td>&(view.description);</td>
    <td class="hours">&(view.hours);</td>
    <td><?php
        if ($view->approved == '0000-00-00 00:00:00')
        {
            ?>
            <input type="checkbox" name="net_nemein_hourview2_approve[<?php echo $view->id; ?>]" value="1" checked="checked" />
            <?php
        }
        else
        {
            $approver = new midgard_person();
            $approver->get_by_id($view->approver);
            $approved = strftime("%x", strtotime($view->approved));
            echo sprintf("Approved by %s on %s", "{$approver->firstname} {$approver->lastname}", $approved);
        }
        ?></td>
</tr>
