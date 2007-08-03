<?php

$date = strftime('%x %X', $data['mail']->received);

$mail_id = $data['mail']->id;
$sender =& $data['sender'];

$img_classname = 'read';
if ($data['mail']->isreplied)
{
    $img_url = MIDCOM_STATIC_URL . '/stock-icons/16x16/stock_mail-replied.png';
    $img_classname = 'replied';
}
else if (! $data['mail']->isread)
{
    $img_url = MIDCOM_STATIC_URL . '/net.nehmer.mail/icons/mail-unread.png';
    $img_classname = 'unread';
}
else
{
    $img_url = MIDCOM_STATIC_URL . '/net.nehmer.mail/icons/mail-read.png';
}

?>

<tr class="&(data['row_class']);">
    <td class="selection"><input type="checkbox" name="selection[]" value="&(mail_id);" id="selection_&(mail_id);" /></td>
    <td class="status"><img src="&(img_url);" class="&(img_classname);"/></td>
    <td class="from"><a href="&(data['new_url']);">&(sender->name);</a></td>
    <td class="subject"><a href="&(data['view_url']);"><?php echo htmlspecialchars($data['mail']->subject);?></a></td>
    <td class="date">&(date);</td>
</tr>
