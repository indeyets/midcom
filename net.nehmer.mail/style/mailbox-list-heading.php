<?php
// Bind the view data, remember the reference assignment:
$view =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h2><?php $view['l10n']->show('your mailboxes')?></h2>