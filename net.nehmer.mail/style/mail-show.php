<?php
// Bind the view data, remember the reference assignment:
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$return_url = $data['return_url'];
$newmail_url = $data['newmail_url'];
$date = strftime('%x %X', $data['mail']->received);
$sender =& $data['sender'];
$msg_id = $data['mail']->guid;

?>
<h2><a href="&(data['return_url']);">&(data['name_translated']);</a></h2>
<h3><?echo htmlspecialchars($data['mail']->subject);?></h3>

<?php if ($data['can_delete']) { ?>
<form action="&(data['manage_url']);" method="post">
<input type="hidden" name="return_url" value="&(data['return_url']);" />
<input type="hidden" name="msg_ids[]" value="&(msg_id);" />
<p>
    <input type="submit"
           name="&(data['delete_submit_button_name']);"
           value="<?php $data['l10n_midcom']->show('delete'); ?>"
    />
</p>
<?php } ?>

<table width='100%'>
  <tr>
    <td width='10%'><?php $data['l10n']->show('date');?></td>
    <td>&(date);</td>
  <tr>
  <tr>
    <td><?php $data['outbox_mode'] ? $data['l10n']->show('to') : $data['l10n']->show('sender');?></td>
    <td><a href="&(newmail_url);">&(sender->name);</a></td>
  <tr>
  <tr>
    <td colspan='2'>&(data['body_formatted']:h);</td>
  </tr>
</table>

