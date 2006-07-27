<?php
global $view, $techsupport;
$temp_p = mgd_get_object_by_guid($view->opener);
$typestr = $techsupport['TN']['type'][$view->type];
$timestr = strftime("%x %X", $view->opened);
?>

<div class="note">
    <div class="noteTitle<?php if ($view->type==3) echo "-email"; ?>"><h3 class="formTitle">&(view.title);</h3></div>
    <div class="noteInfo">
        <p>By &(temp_p.name); on &(timestr);</a></p>
    </div>

    <div class="noteBody">
        <p>&(view.description:h);</p>
    </div>

<?php
list_obj_att($view, 1); //List ticket note attachments
?>

    <div class="noteFooter">

<?php
if (is_array($view->sent_to)) {
    while (list ($k, $data) = each ($view->sent_to)) {
        if (is_array($data)) {
            $labelStr=sprintf('Sent as email to %s', $data[to]);
            echo "<p>$labelStr</p>\n";
        }
    }
}
?>

    </div>

</div>

