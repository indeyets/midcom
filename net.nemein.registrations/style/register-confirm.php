<?php
// Available request keys:
// event, controller

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$event =& $data['event'];
$title = sprintf($data['l10n']->get('register for %s'), $event->title);
// $event_dm =& $event->get_datamanager();
?>

<h2><?php echo $data['topic']->extra; ?>: &(title);</h2>
<p>
  &(event.description:h);
</p>

<p><?php $data['l10n']->show('please confirm your answers'); ?></p>

<?php 
// This form is frozen, but we need to operations handling so display_view isn't going to cut it
$data['controller']->display_form(); 
?>