<?php
$mailboxes =& $data['mailboxes'];
?>

<h2><?php echo $data['l10n']->get('your mailboxes'); ?></h2>

<ul>
<?php
foreach ($mailboxes as $mailbox)
{
    $anchor_prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
    $msgcount = $mailbox->get_message_count();
    if ($mailbox->quota > 0)
    {
        $quota_string = "{$msgcount}/{$mailbox->quota}";
    }
    else
    {
        $quota_string = $msgcount;
    }
    
    echo '<li>';
    if ($_MIDCOM->auth->can_do('midgard:update', $mailbox))
    {
        echo "<a href='{$anchor_prefix}admin/edit/{$mailbox->guid}'>{$mailbox->name}</a>";
    }
    else
    {
        echo $mailbox->name;
    }
    
    echo " ({$quota_string})";

    if ($_MIDCOM->auth->can_do('midgard:delete', $mailbox))
    {
        echo " <a href='{$anchor_prefix}admin/delete/{$mailbox->guid}'>" . $data['l10n_midcom']->get('delete') . '</a>';
    }
    
    echo "</li>\n";
}
?>

</ul>