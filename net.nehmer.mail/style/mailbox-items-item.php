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
    <?php
    if ($data['mail']->status == NET_NEHMER_MAIL_STATUS_SENT)
    {
    ?>
    <td class="to"><?php $data['l10n']->show($data['mail']->list_receivers()); ?></td>
    <?php
    }
    else
    {
    ?>
    <td class="selection"><input type="checkbox" name="selection[]" value="&(mail_id);" id="selection_&(mail_id);" /></td>
    <td class="status"><img src="&(img_url);" class="&(img_classname);"/></td>
    <td class="from"><a href="&(data['new_url']);">&(sender->name);</a></td>
    <?php
    }
    ?>
    <td class="subject"><a href="&(data['view_url']);"><?php echo htmlspecialchars($data['mail']->subject);?></a></td>
    <td class="date">&(date);</td>
</tr>
