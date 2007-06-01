<?php
// Available request keys:
// events, event, register_url, registration_url, view_url, registration_allowed, registration_url

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$event =& $data['event'];
// $event_dm =& $event->get_datamanager();
?>

<p>
  <span style="font-weight: bold;"><a href="&(data['view_url']);">&(event.title);</a></span> <?php
if ($event->is_open())
{
    echo '(<span style="color: green;">' . $data['l10n']->get('open') . '</span>)';
}
else
{
    echo '(<span style="color: red;">' . $data['l10n']->get('closed') . '</span>)';
}
?>
    <br />
<?php
if ($data['registration_open'])
{
    if (   $_MIDCOM->auth->user
        && ! $data['registration_allowed'])
    {
        $data['l10n']->show('you do not have the permissions to register for this event.');
    }
    else if ($data['register_url'])
    {
?>
    <a href="&(data['register_url']);"><?php $data['l10n']->show('register for this event'); ?></a>
<?php
    }
    else
    {
?>
    <a href="&(data['registration_url']);"><?php $data['l10n']->show('you have already registered for this event.'); ?></a>
<?php
    }
}
else if ($data['registration_url'])
{
?>
    <a href="&(data['registration_url']);"><?php $data['l10n']->show('view your registration'); ?></a>
<?php } ?>
</p>