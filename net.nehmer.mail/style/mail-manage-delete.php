<?php

?>
<div class="mailbox_content" id="message-delete">
    <div class="mailbox_inner_content">
        <div class="sender_photo">
        </div>
        <div class="message-details">
            <div class="headers">
                <span class="subject"><?php echo sprintf($data['l10n']->get('really delete mail %s?'), $data['mail']->subject); ?></span><br />
            </div>
            <div class="actions">
                <div class="left">
                    <form action="&(data['delete_url']);" method="post">
                    <input type="submit" name="&(data['delete_button_name']);" value="<?php $data['l10n_midcom']->show('delete'); ?>" />
                    </form>
                </div>
            </div>
        </div>
        <div style="clear:both;"></div>     
    </div>
</div>