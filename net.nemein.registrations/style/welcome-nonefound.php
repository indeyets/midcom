<?php
// Available request keys:
// events

$data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<p><?echo $data['l10n']->get('no events found'); ?></p>