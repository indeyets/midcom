<?php
global $view;
global $view_gallery;
global $view_thumbs_x;
global $view_curcol;
$data = $view->datamanager->data;
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$attachmentserver = $GLOBALS["midcom"]->midgard->self . "midcom-serveattachmentguid-";
?>
<td align="center" class="gallery">
<a href="&(prefix);"><img src="&(attachmentserver);&(view.thumbnail);/thumbnail_&(data['name']);" alt="&(data["title"]);" title="&(data["title"]);" /><br />
&(view_gallery->extra);</a>
</td>
