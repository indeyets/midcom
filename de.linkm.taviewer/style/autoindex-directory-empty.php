<?php
// Bind the view data, remember the reference assignment:
$view =& $_MIDCOM->get_custom_context_data('request_data');
?>
<tr>
<td colspan="5"><?echo $view['l10n']->get("no files in this directory");?></td>
</tr>