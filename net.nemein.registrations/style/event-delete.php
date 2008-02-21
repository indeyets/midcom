<?php
// Available request keys:
// event, datamanager, view_url, edit_url, delete_url, list_registrations_url

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$event =& $data['event'];
$controller =& $event->create_simple_controller();
?>
<h2><?php echo $data['topic']->extra; ?>: &(event.title);</h2>

<h3><?php $data['l10n']->show('delete event'); ?>:</h3>
<form action='' method='post'><p>
    <input type='submit' name='net_nemein_registrations_deleteok' value="<?php $data['l10n_midcom']->show('yes'); ?>" />
    <input type='submit' name='net_nemein_registrations_deletecancel' value="<?php $data['l10n_midcom']->show('no'); ?>" />
</p></form>

<?php $controller->formmanager->display_view(); ?>
