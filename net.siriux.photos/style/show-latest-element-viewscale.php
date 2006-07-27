<?php
global $view;
$data = $view->datamanager->data;
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$attachmentserver = $GLOBALS["midcom"]->midgard->self . "midcom-serveattachmentguid-";
?>
<table border="0" align="left">
<tr>
<td>
<a href="&(prefix);&(data['name']);.html"><img src="&(attachmentserver);&(view.view);/&(data['name']);" alt="" /><br />
&(data['title']);</a>
</td>
</tr>
</table>
