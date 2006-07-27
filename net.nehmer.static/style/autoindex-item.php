<?php
// Available request keys: filename, data

$data =& $_MIDCOM->get_custom_context_data('request_data');
$object = $data['data'];
?>
  <tr>
    <td><a href="&(object['url']);">&(object["name"]);</a></td>
    <td>&(object["desc"]);</td>
    <td>&(object["type"]);</td>
    <td>&(object["size"]); Bytes</td>
    <td>&(object["lastmod"]);</td>
  </tr>
