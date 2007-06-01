<?php
// Available request keys: article, datamanager, edit_url, delete_url, create_urls

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$photo =& $data['photo'];
?>
<li class="error"><?php echo sprintf($data['l10n']->get('failure when processing photo "%s" (GUID: %s)'), $photo->title, $photo->guid); ?></li>