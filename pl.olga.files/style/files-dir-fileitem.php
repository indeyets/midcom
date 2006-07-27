<?php
global $view;
global $midcom;
$prefix = $midcom->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
 <tr>
  <td><a href="&(prefix);&(view["dir"]);/&(view["name"]);">&(view["name"]);</a></td>
  <td align="right">&(view["size"]);</td>
  <td align="right">&(view["mtime"]);</td>
 </tr>
