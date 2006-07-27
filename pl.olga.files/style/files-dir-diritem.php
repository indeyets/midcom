<?php
global $view;
global $midcom;
$prefix = $midcom->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$url=$prefix.$view["dir"]."/".$view["name"];
$url=ereg_replace("//","/",$url);
?>
 <tr>
  <td><a href="&(url);">&(view["name"]);</a></td>
  <td align="right">&nbsp;</td>
  <td align="right">&(view["mtime"]);</td>
 </tr>
