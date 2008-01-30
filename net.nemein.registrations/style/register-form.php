<?php
// Available request keys:
// event, controller

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$event =& $data['event'];
$title = sprintf($data['l10n']->get('register for %s'), $event->title);
// $event_dm =& $event->get_datamanager();
?>

<?php
if (!$data['admin_mode'])
{
    // Standard
?>
<h2><?php echo $data['topic']->extra; ?>: &(title);</h2>
<p>
  &(event.description:h);
</p>

<p><?php $data['l10n']->show('please answer these questions to complete registration'); ?>:</p>
<?php
}
else
{
    echo "<h2>{$event->title}</h2>\n";
}
?>
<?php $data['controller']->display_form(); ?>