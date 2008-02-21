<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
if (!isset($data['reject_reason']))
{
    $data['reject_reason'] = $data['l10n']->get('no reason given');
}
$registration =& $data['registration'];
$event =& $data['event'];
$registrar =& $data['registrar'];
$event_dm =& $data['event_dm'];
$registration_dm =& $data['registration_dm'];
$registrar_dm =& $data['registrar_dm'];
?>Dear &(registrar.name);,

we have rejected your registration for:

  &(event.title);
  
  (&(data['event_url']);)


Reason:

&(data['reject_reason']);


Personal data:

<?php echo $event->dm_array_to_string($registrar_dm); ?>


Additional information:

<?php echo $event->dm_array_to_string($registration_dm); ?>
