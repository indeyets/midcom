<?php
// Bind the view data, remember the reference assignment:
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$return_url = $data['return_url'];
$newmail_url = $data['newmail_url'];
$date = strftime('%x %X', $data['mail']->received);
$receiver =& $data['receiver'];

?>
<h2><a href="&(data['return_url']);">&(data['name_translated']);</a></h2>

<p><?php $data['l10n']->show('mail sent successfully'); ?></p>
<?php if ($data['return_to']) { ?>
<p><a href="&(data['return_to']);"><?php $data['l10n_midcom']->show('back'); ?></a></p>
<?php } ?>

<h3><?php echo htmlspecialchars($data['mail']->subject);?></h3>

<table width='100%'>
  <tr>
    <td width='10%'><?php $data['l10n']->show('date');?></td>
    <td>&(date);</td>
  <tr>
  <tr>
    <td><?php $data['l10n']->show('to');?></td>
    <td><a href="&(newmail_url);">&(receiver->name);</a></td>
  <tr>
  <tr>
    <td colspan='2'>&(data['body_formatted']:h);</td>
  </tr>
</table>

