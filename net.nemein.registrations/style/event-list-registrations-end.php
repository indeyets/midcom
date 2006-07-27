<?php
// Available request keys:
// event, view_url, edit_url, delete_url
// registrations

$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
</ul>

<p><a href="&(data['view_url']);"><?php $data['l10n_midcom']->show('back'); ?></a></p>