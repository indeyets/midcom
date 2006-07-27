<?php
// Bind the view data, remember the reference assignment:
$view =& $_MIDCOM->get_custom_context_data('request_data');

$url = $view['url'];
$newmail_url = $view['newmail_url'];
$msg_id = $view['mail']->guid;
$reply_url = $view['reply_url'];
$date = strftime('%x %X', $view['mail']->received);
$sender =& $view['sender'];

if ($view['mail']->isreplied)
{
    $img_url = MIDCOM_STATIC_URL . '/stock-icons/16x16/stock_mail-replied.png';
}
else if ($view['mail']->isread)
{
    $img_url = MIDCOM_STATIC_URL . '/stock-icons/16x16/stock_mail-open.png';
}
else
{
    $img_url = MIDCOM_STATIC_URL . '/stock-icons/16x16/stock_mail.png';
}

?>
<tr class='<?php echo $view['background_class']; ?>'>
  <td align='left' class='maildate' nowrap='nowrap'>&(date);</td>
  <td align='left' class='mailsender'><a href="&(newmail_url);">&(sender->name);</a></td>
  <td align='left' class='mailsubject'><a href="&(url);"><img src="&(img_url);" style="margin-right: 0.25em;" /><?echo htmlspecialchars($view['mail']->subject);?></a></td>
  <td align='center' class='mailcommands' nowrap='nowrap'>
    <a href="&(newmail_url);"><img src="<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/16x16/stock_mail-send.png"/></a>
    <a href="&(reply_url);"><img src="<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/16x16/stock_mail-reply.png"/></a>
    <input type="checkbox" name="msg_ids[]" value="&(msg_id);" />
  </td>
</tr>