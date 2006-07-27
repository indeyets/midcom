<?php
// Bind the view data, remember the reference assignment:
$view =& $_MIDCOM->get_custom_context_data('request_data');

?>
<p><?php $view['l10n']->show('mailbox empty.')?></p>