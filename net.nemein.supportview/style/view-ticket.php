<?php
global $view;
$desc = nl2br($view->description);
?>
<div class="net_nemein_supportview">
<div class="ticket">
<h1>&(view.title);</h1>

<dl>
  <dt>Opened</dt>
    <dd><?php echo strftime("%x",$view->opened); ?></dd>
  <dt>Last action time</dt>
    <dd><?php echo strftime("%x %X",$view->lastaction['stamp']); ?></dd>
  <dt>Assignee</dt>
<dd>
<?php 
if ($view->assignee) {
    $assignee = mgd_get_object_by_guid($view->assignee);
    if ($assignee->name != "") {
        echo $assignee->name;
    } else {
        echo "Not Found";
    } 
} else {
    echo "Not assigned";
}
?>
</dd>
  <dt>Sent by</dt>
<dd>
<?php
/*
echo $view->contacts['name'];
if (!$view->contacts['name']) echo "&nbsp;";
*/
if (   isset($view->contacts['name'])
    && !empty($view->contacts['name']))
{
    echo $view->contacts['name'];
}
else if (  isset($view->contacts['email'])
        && !empty($view->contacts['email']))
{
    echo $view->contacts['email'];
}
else
{
    echo '&nbsp;';
}
?>
</dd>
</dl>

<div class="description">
    &(desc:h);
</div>
<?php
list_obj_att($view,1); //This will produce attachment list, function in the helper.php
?>
</div>
</div>