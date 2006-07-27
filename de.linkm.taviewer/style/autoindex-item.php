<?php
global $view_l10n, $view_l10n_midcom, $view, $view_filename;
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
  <tr>
    <td><a href="&(view['url']);">&(view["name"]);</a></td>
    <td>&(view["desc"]);</td>
    <td>&(view["type"]);</td>
    <td>&(view["size"]); Bytes</td>
    <td>&(view["lastmod"]);</td>
  </tr>
