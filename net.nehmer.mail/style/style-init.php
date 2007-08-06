<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

$trash_item_class = '';
if ($data['in_trash_view'])
{
    $trash_item_class = 'selected';
}
$trash_url = "{$prefix}mail/admin/trash";

$compose_item_class = '';
if ($data['in_compose_view'])
{
    $compose_item_class = 'selected';
}
$compose_url = "{$prefix}mail/compose/new";
?>
<div class="mailbox_navigation">
    <ul>
        <?php
        foreach ($data['mailboxes'] as $mailbox)
        {
            $view_url = $prefix . $mailbox->get_view_url();

            $class = '';
            if (   isset($data['mailbox'])
                && $data['mailbox']->id == $mailbox->id)
            {
                $class = 'selected';
            }
        ?>
        <li class="&(class);"><a href="&(view_url);"><?php $data['l10n']->show($mailbox->name); ?></a></li>
        <li class="separator"></li>
        <?php
        }
        ?>
        <li class="&(trash_item_class);"><a href="&(trash_url);"><?php $data['l10n']->show('garbage bin'); ?></a></li>
        <li class="separator"></li>
        <li class="&(compose_item_class);"><a href="&(compose_url);"><?php $data['l10n']->show('compose message'); ?></a></li>
        <li class="separator"></li>
    </ul>               
</div>