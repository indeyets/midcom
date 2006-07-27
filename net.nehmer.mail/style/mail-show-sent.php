<?php
// Bind the view data, remember the reference assignment:
$view =& $_MIDCOM->get_custom_context_data('request_data');

$return_url = $view['return_url'];
$newmail_url = $view['newmail_url'];
$date = strftime('%x %X', $view['mail']->received);
$receiver =& $view['receiver'];

?>
<h2><a href="&(view['return_url']);">&(view['name_translated']);</a></h2>

<p><?php $view['l10n']->show('mail sent successfully'); ?></p>
<?php if ($view['return_to']) { ?>
<p><a href="&(view['return_to']);"><?php $view['l10n_midcom']->show('back'); ?></a></p>
<?php } ?>

<h3><?php echo htmlspecialchars($view['mail']->subject);?></h3>

<table width='100%'>
  <tr>
    <td width='10%'><?php $view['l10n']->show('date');?></td>
    <td>&(date);</td>
  <tr>
  <tr>
    <td><?php $view['l10n']->show('to');?></td>
    <td><a href="&(newmail_url);">&(receiver->name);</a></td>
  <tr>
  <tr>
    <td colspan='2'>&(view['body_formatted']:h);</td>
  </tr>
</table>

