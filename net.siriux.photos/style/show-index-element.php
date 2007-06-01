<?php
global $view;
global $view_thumbs_x;
global $view_curcol;
$data = $view->datamanager->data;
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$attachmentserver = $_MIDCOM->midgard->self . "midcom-serveattachmentguid-";

$view_curcol++;

if ($view_curcol > $view_thumbs_x) {
    ?></tr><tr><?php
    $view_curcol = 1;
}
?>
<td align="center">
<a href="&(prefix);&(data['name']);"><img src="&(attachmentserver);&(view.thumbnail);/thumbnail_&(data['name']);" alt="" /><br />
&(data['title']);</a>
</td>
