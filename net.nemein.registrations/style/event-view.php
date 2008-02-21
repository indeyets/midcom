<?php
// Available request keys:
// event, datamanager, view_url, edit_url, delete_url, list_registrations_url

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$event =& $data['event'];
$controller =& $event->create_simple_controller();
?>
<h2><?php echo $data['topic']->extra; ?>: &(event.title);</h2>

<?php
$controller->formmanager->display_view();
midcom_show_style('event-toolbar');
?>