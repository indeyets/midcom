<?php

$sender =& $_MIDCOM->auth->user->get_storage();
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

?>
<div class="mailbox_content" id="message-compose-&(data['compose_type']);">
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
                <h2>&(data['heading']);</h2>
            </div>
            <div class="content">
                <?php 
                $data['controller']->display_form(); 
                ?>
            </div>
        </div>
        <div style="clear:both;"></div>     
    </div>
</div>