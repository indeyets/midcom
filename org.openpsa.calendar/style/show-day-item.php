<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());

$event = new org_openpsa_calendarwidget_event($data['event']);
$event->link = '#';
$event->onclick = org_openpsa_calendar_interface::calendar_editevent_js($data['event']->guid, $node);
echo $event->render('li');
?>