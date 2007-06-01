<?php
global $view;
$data = $view->datamanager->data;
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$attachmentserver = $_MIDCOM->midgard->self . "midcom-serveattachmentguid-";
?>
<table border="0" align="left">
<tr>
<td>
<a href="&(prefix);&(data['name']);.html"><img src="&(attachmentserver);&(view.thumbnail);/thumbnail_&(data['name']);" alt="" /><br />
&(data['title']);</a>
</td>
</tr>
</table>
