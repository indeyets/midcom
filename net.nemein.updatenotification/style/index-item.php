<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$title = $data['node']->extra;
$id = $data['node']->id;
$guid = $data['node']->guid;
$person_subscriptions = explode(',',$data['user']->get_parameter('net.nemein.updatenotification:subscribe', $guid));

?>
<li><?php
foreach($data['preferred_notification_methods'] as $notification_method)
{
$selected = '';
if(in_array($notification_method,$person_subscriptions))
$selected = ' checked';
 ?><input class="net_nemein_updatenotification_select_&(notification_method);" type="checkbox" &(selected); value="&(notification_method);" name="net_nemein_updatenotification[&(guid);]" /><?php
 }
?>&nbsp;&(title);</li>