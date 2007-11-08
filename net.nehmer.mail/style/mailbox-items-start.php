<?php
?>
<div class="mailbox_content" id="<?php echo $data['mailbox_classname']; ?>-list">
    <div class="mailbox_inner_content">
        <?php
        if (isset($data['qb_pager'])
            && is_object($data['qb_pager'])
            && method_exists($data['qb_pager'], 'show_pages'))
        {
            echo "<div class=\"net_nehmer_mail_pager\">\n";
            $data['qb_pager']->show_pages();
            echo "</div>\n";
        }
        ?>
        <form action="&(data['action_handler_url']);" method="post">
            <input type="hidden" name="return_url" value="&(data['return_url']);" />
        <table border="0" cellspacing="0" cellpadding="0" class="mail_list &(data['mailbox_classname']);">
            <thead>
                <tr>
                    <?php
                    if ($data['mailbox_classname'] == 'outbox')
                    {
                    ?>
                    <th class="to"><?php $data['l10n']->show('to'); ?></th>
                    <?php
                    }
                    else
                    {
                    ?>
                    <th class="selection"><input type="checkbox" name="select_all" value="" id="select_all" onclick="toggle_checkboxes();" /></th>
                    <th class="status">&nbsp;</th>
                    <th class="from"><?php $data['l10n']->show('from'); ?></th>
                    <?php
                    }
                    ?>
                    <th class="subject"><?php $data['l10n']->show('subject'); ?></th>
                    <th class="date"><?php $data['l10n']->show('date'); ?></th>
                </tr>
            </thead>
            <tbody>