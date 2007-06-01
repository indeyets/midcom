<?php
// Available request keys: persons, person, datamanager, view_url

// $data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['datamanager']->get_content_html();
?>
<td valign="bottom">
<?php if ($view['image']) { ?>
    <div style="align: center;">&(view['image']:h);</div>
<?php } ?>
<p>
<b><a href="&(data['view_url']);">&(view['name']);</a></b><br />
<?php if ($view['email']) { ?>
  Email: <a href="mailto:&(view['email']);">&(view['email']);</a><br />
<?php } ?>
</p>
</td>