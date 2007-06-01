<?php
// Available request keys: event, datamanager

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['view_resource'];

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<tr>
    <td><a href="&(prefix);view/<?php echo $data['resource']->name; ?>/">&(view['title']:h);</a></td>
    <td>&(view['capacity']);</td>
    <td>&(view['location']);</td>
</tr>
