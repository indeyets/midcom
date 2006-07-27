<?php
global $view;
$view_date = strftime("%x",$view->start);
$reporter = mgd_get_object_by_guid($view->person);
?>
<tr<?php
if ($GLOBALS["view_even"])
{
    echo " class=\"even\"";
}
?>>
    <td>&(view_date);</td>
    <td>&(reporter.rname);</td>
    <td>&(view.description);</td>
    <td class="hours">&(view.hours);</td>
    <td><?php
        if (!$view->approved)
        {
            ?>
            <input type="checkbox" name="net_nemein_hourview_approve[<?php echo $view->id; ?>]" value="1" checked="checked" />
            <?php
        }
        else
        {
            $approver = mgd_get_object_by_guid($view->approver);
            $approved = strftime("%x",$view->approved);
            echo sprintf("Approved by %s on %s",$approver->name,$approved);
        }
        ?></td>
</tr>
