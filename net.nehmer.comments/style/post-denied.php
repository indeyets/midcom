<?php
// Available request data: comments, objectguid.
$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<a name="net_nehmer_comments_post_&(data['objectguid']);"></a>
<h3><?php $data['l10n']->show('post a comment'); ?>:</h3>

<p><?php $data['l10n']->show('permission-denied message'); ?></p>
