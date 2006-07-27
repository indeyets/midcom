<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
// Available request data: entries, mode
?>
<h2><?php $data['l10n']->show("your {$data['mode']}s"); ?></h2>

<ul>