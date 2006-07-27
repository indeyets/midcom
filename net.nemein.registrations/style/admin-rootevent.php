<?php
// Available request keys:
// root_event, schemadb, processing_msg, create_new_value, select_action, select_guid, root_events

$data =& $_MIDCOM->get_custom_context_data('request_data');

$selected_guid = ($data['root_event']) ? $data['root_event']->guid : false;
?>
<h2><?php echo $data['topic']->extra; ?>: <?php $data['l10n']->show('manage root event'); ?></h2>

<?php if ($data['processing_msg']) { ?>
<div class='processing_message'>&(data['processing_msg']);</div>
<?php } ?>

<form method='POST' action=''>

<h3><?php $data['l10n']->show('use existing root event'); ?></h3>
<p>
<select class="dropdown" name="&(data['select_guid']);" size="1">
<?php if (! $selected_guid) { ?>
    <option value="&(data['create_new_value']);" selected="selected"><?php $data['l10n']->show('create an event'); ?></option>
<?php } else { ?>
    <option value="&(data['create_new_value']);"><?php $data['l10n']->show('create an event'); ?></option>
<?php
}
foreach ($data['root_events'] as $event)
{
    $selected = ($event->guid == $selected_guid) ? 'selected="selected"' : '';
    ?>
    <option value="&(event.guid);" &(selected:h);>&(event.title);</option>
<?php } ?>
</select>
<input type="submit" name="&(data['select_action']);" value="<?php $data['l10n_midcom']->show('select');?>" />
</p>