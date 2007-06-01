<?php
// Available request keys: groups, person, datamanager, view_url

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['datamanager']->get_content_html();
?>
<td valign="bottom">
<p>
<b><a href="&(data['view_url']);">&(view['official']);</a></b><br />
<?php if ($view['email']) { ?>
  Email: <a href="mailto:&(view['email']);">&(view['email']);</a><br />
<?php } ?>
</p>
</td>