<?php
// Available request keys:
// event, controller, view_url, edit_url, delete_url, list_registrations_url

$data =& $_MIDCOM->get_custom_context_data('request_data');
$event =& $data['event'];
$controller =& $data['controller'];
?>
<h2><?php echo $data['topic']->extra; ?>: &(event.title);</h2>

<?php $controller->display_form(); ?>