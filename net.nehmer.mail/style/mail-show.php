<?php
// Bind the view data, remember the reference assignment:
$view =& $_MIDCOM->get_custom_context_data('request_data');

$return_url = $view['return_url'];
$newmail_url = $view['newmail_url'];
$date = strftime('%x %X', $view['mail']->received);
$sender =& $view['sender'];
$msg_id = $view['mail']->guid;

?>
<h2><a href="&(view['return_url']);">&(view['name_translated']);</a></h2>
<h3><?echo htmlspecialchars($view['mail']->subject);?></h3>

<?php if ($view['can_delete']) { ?>
<form action="&(view['manage_url']);" method="post">
<input type="hidden" name="return_url" value="&(view['return_url']);" />
<input type="hidden" name="msg_ids[]" value="&(msg_id);" />
<p>
    <input type="submit"
           name="&(view['delete_submit_button_name']);"
           value="<?php $view['l10n_midcom']->show('delete'); ?>"
    />
</p>
<?php } ?>

<table width='100%'>
  <tr>
    <td width='10%'><?php $view['l10n']->show('date');?></td>
    <td>&(date);</td>
  <tr>
  <tr>
    <td><?php $view['outbox_mode'] ? $view['l10n']->show('to') : $view['l10n']->show('sender');?></td>
    <td><a href="&(newmail_url);">&(sender->name);</a></td>
  <tr>
  <tr>
    <td colspan='2'>&(view['body_formatted']:h);</td>
  </tr>
</table>

