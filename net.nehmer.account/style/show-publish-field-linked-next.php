<?php
// The available request keys can be found in the components' API documentation
// of net_nehmer_account_handler_publish
//
// Bind the view data, remember the reference assignment:
$data =& $_MIDCOM->get_custom_context_data('request_data');
$field =& $data['current_field'];
?>
<tr>
    <td style='padding-right: 1em;' nowrap='nowrap'>&(field['title']:h);</td>
    <td style='padding-right: 1em;'>&(field['content']:h);</td>
</tr>