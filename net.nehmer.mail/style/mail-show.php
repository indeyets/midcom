<?php
// Bind the view data, remember the reference assignment:
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$sender =& $data['sender'];
$mail_guid = $data['mail']->guid;

$sender_avatar = $sender->get_attachment('avatar');
$sender_avatar_thumb = $sender->get_attachment('avatar_thumbnail');

$att_prefix = $_MIDCOM->get_page_prefix();
if ($sender_avatar)
{
    $avatar_url = "{$att_prefix}midcom-serveattachmentguid-{$sender_avatar->guid}/avatar";
}
if ($sender_avatar_thumb)
{
    $avatar_thumb_url = "{$att_prefix}midcom-serveattachmentguid-{$sender_avatar_thumb->guid}/avatar_thumbnail";    
}

$date = strftime('%x %X', $data['mail']->received);
?>

<div class="mailbox_content" id="message-view">
    <div class="mailbox_inner_content">
        <div class="sender_photo">
            <?php
            if ($sender_avatar)
            {
            ?>
            <img src="&(avatar_url);" title="&(sender->name);">
            <?php
            }
            ?>
        </div>
        <div class="message-details">
            <div class="headers">
                <span class="subject"><?php $data['l10n']->show('subject'); ?>: <?php echo htmlspecialchars($data['mail']->subject);?></span><br />
                <span class="date"><?php $data['l10n']->show('date'); ?>: &(date);</span><br />
                <span class="from"><?php $data['l10n']->show('from'); ?>: <a href="&(data['new_url']);">&(sender->name);</a></span><br />
                <?php
                if ($data['is_sent'])
                {
                ?>
                <div class="receivers">
                    <span class="to"><?php $data['l10n']->show('to'); ?>:</span>
                    <ul>
                    <?php
                    foreach ($data['receivers'] as $i => $receiver)
                    {
                    ?>
                    <li>
                        <span class="receiver">&(receiver->name);</span>
                    </li>
                    <?php
                    }
                    ?>
                    </ul>
                </div>
                <?php
                }
                ?>
            </div>
            <div class="content">
                &(data['body_formatted']:h);
            </div>
            <div class="actions">
                <div class="left">
                    <?php
                    if (! $data['is_sent'])
                    {
                    ?>
                    <input type="button" name="reply" value="<?php $data['l10n']->show('reply'); ?>" id="reply" onclick="window.location='&(data['reply_url']);'">
                    <input type="button" name="replyall" value="<?php $data['l10n']->show('reply all'); ?>" id="replyall" onclick="window.location='&(data['replyall_url']);'">
                    <?php
                    }
                    if ($data['can_delete'])
                    {
                    ?>
                    <form action="&(data['delete_url']);" method="post">
                    <input type="hidden" name="return_url" value="&(data['return_url']);" />
                    <input type="hidden" name="msg_ids[]" value="&(mail_guid);" />
                    <input type="submit" name="&(data['delete_submit_button_name']);" value="<?php $data['l10n_midcom']->show('delete'); ?>" />
                    </form>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <div style="clear:both;"></div>     
    </div>
</div>

