<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$entry =& $data['entry'];

// Available request data: entry, entries, datamanager, mode, view_url, edit_url, delete_url
?>
<li>
    <a href="&(data['view_url']);">&(entry.title);:</a>&nbsp;&nbsp;
    <a href="&(data['edit_url']);"><?php $data['l10n_midcom']->show('edit'); ?></a>&nbsp;&nbsp;
    <a href="&(data['delete_url']);"><?php $data['l10n_midcom']->show('delete'); ?></a>
</li>