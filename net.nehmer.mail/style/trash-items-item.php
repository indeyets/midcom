<?php

$date = strftime('%x %X', $data['mail']->received);

$mail_id = $data['mail']->id;
$sender =& $data['sender'];

$img_url = '';
$img_classname = 'read';
if (   $data['mail']->status == NET_NEHMER_MAIL_STATUS_READ)
{
    $img_url = MIDCOM_STATIC_URL . '/net.nehmer.mail/icons/mail-read.png';
    $img_classname = 'read';
}
else if ($data['mail']->status == NET_NEHMER_MAIL_STATUS_UNREAD)
{
    $img_url = MIDCOM_STATIC_URL . '/net.nehmer.mail/icons/mail-unread.png';
    $img_classname = 'unread';
}
else if ($data['mail']->status == NET_NEHMER_MAIL_STATUS_STARRED)
{
    $img_url = MIDCOM_STATIC_URL . '/net.nehmer.mail/icons/mail-starred.png';
    $img_classname = 'starred';
}

?>

<tr class="&(data['row_class']);">
    <td class="selection"><input type="checkbox" name="selections[]" value="&(mail_id);" id="selection_&(mail_id);" /></td>
    <td class="status"><img src="&(img_url);" class="&(img_classname);"/></td>
    <td class="from">&(sender->name);</td>
    <td class="subject"><?php echo htmlspecialchars($data['mail']->subject);?></td>
    <td class="date">&(date);</td>
</tr>
