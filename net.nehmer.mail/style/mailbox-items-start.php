<?php
?>
<div class="mailbox_content" id="<?php echo $data['mailbox_classname']; ?>-list">
    <div class="mailbox_inner_content">
        <table border="0" cellspacing="0" cellpadding="0" class="mail_list &(data['mailbox_classname']);">
            <thead>
                <tr>
                    <th class="selection"><input type="checkbox" name="select_all" value="" id="select_all" onclick="toggle_checkboxes();" /></th>
                    <th class="status">&nbsp;</th>
                    <th class="from"><?php $data['l10n']->show('from'); ?></th>
                    <th class="subject"><?php $data['l10n']->show('subject'); ?></th>
                    <th class="date"><?php $data['l10n']->show('date'); ?></th>
                </tr>
            </thead>
            <tbody>