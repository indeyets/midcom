<?php
// Bind the view data, remember the reference assignment:
$view =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h2>&(view['name_translated']);</h2>
<p><a href="&(prefix);"><?php $view['l10n']->show('your mailboxes')?></a></p>