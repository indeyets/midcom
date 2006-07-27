<?php
// Bind the data data, remember the reference assignment:
$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<tr>
<td colspan='5'><?php $data['l10n']->show('no files in this directory');?></td>
</tr>