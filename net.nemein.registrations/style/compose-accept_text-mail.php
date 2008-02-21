<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$registration =& $data['registration'];
$event =& $data['event'];
$registrar =& $data['registrar'];
$event_dm =& $data['event_dm'];
$registration_dm =& $data['registration_dm'];
$registrar_dm =& $data['registrar_dm'];
?>Dear &(registrar.name);,

we have received your registration for:

  &(event.title);
  
  (&(data['event_url']);)


Personal data:

<?php echo $event->dm_array_to_string($registrar_dm); ?>


Additional information:

<?php echo $event->dm_array_to_string($registration_dm); ?>