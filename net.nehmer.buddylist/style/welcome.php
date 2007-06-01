<?php
// Available request keys:
// buddies, buddies_meta, delete_form_action
//
// Available metadata keys, see net_nehmer_buddylist_handler_welcome::_buddies_meta

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

?>

<h2><?php echo $data['topic']->extra; ?></h2>

<?php if ($data['buddies']) { ?>

<form action="&(data['delete_form_action']);" method="post" />
<table border="0" width="100%">
<tr>
   <th width="50%" align="left"><?php $data['l10n_midcom']->show('username'); ?></th>
   <th align="center"><?php $data['l10n_midcom']->show('online state'); ?></th>
   <th align="center">&nbsp;</th>
   <th align="center"><?php $data['l10n_midcom']->show('delete'); ?></th>
</tr>

<?php
    foreach ($data['buddies'] as $username => $copy)
    {
        $buddy_meta = $data['buddies_meta'][$username];
        $user =& $data['buddies'][$username];
?>
    <tr>
<?php if ($buddy_meta['view_account_url']) { ?>
        <td><a href="&(buddy_meta['view_account_url']);">&(username);</a></td>
<?php } else { ?>
        <td>&(username);</td>
<?php } ?>
        <td align="center"><?php $data['l10n_midcom']->show($buddy_meta['is_online'] ? 'online' : 'offline'); ?></td>
<?php if ($buddy_meta['new_mail_url']) { ?>
        <td align="center"><a href="&(buddy_meta['new_mail_url']);"><?php $data['l10n_midcom']->show('write mail'); ?></a></td>
<?php } else { ?>
        <td align="center">&nbsp;</td>
<?php } ?>
        <td align="center">
            <input type="checkbox"
                   name="&(buddy_meta['delete_checkbox_name']);"
            />
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td align="center">
            <input type="submit"
                   name="&(data['delete_submit_button_name']);"
                   value="<?php $data['l10n']->show('delete selected'); ?>"
            />
        </td>
    </tr>
</table>
</form>
<?php
    }
}
else
{
?>
    <p><?php $data['l10n']->show('no buddies found.'); ?></p>
<?php } ?>

<?php if (net_nehmer_buddylist_entry::get_unapproved_count() > 0) { ?>
    <p><a href="&(prefix);pending/list.html"><?php $data['l10n']->show('new buddy requests pending.'); ?></a></p>
<?php } ?>
