<?php
// Remember the reference assignment
$data =& $_MIDCOM->get_custom_context_data('request_data');

// Available request keys: datamanager, fields, schema, account, avatar, avatar_thumbnail, profile_url,
//     edit_url, account_revised, account_published, avatar_url, avatar_thumbnail_url, onlinestate_checked
?>

<h2><?php $data['l10n']->show('publish account details'); ?></h2>

<p><?php $data['l10n']->show('publishing successful.'); ?></p>