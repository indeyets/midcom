<?php
/*
 * Created on Aug 22, 2005
 *
 */
$request_data =& $_MIDCOM->get_custom_context_data('request_data');
$diff   = $request_data['diff'];
$latest = $request_data['latest_revision'];
$comment= $request_data['comment'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1>&(request_data['view_title']);</h1>
<p>
<? echo $request_data['l10n']->get('Unchanged attributes are not shown.'); ?>
</p>
<table width="100%" colspace="0" class="ais" border="0" >

<thead class="ais">
<tr >
    <td class="ais">
       <b> Attribute</b>
    </td>
    <td class="ais">
       <b> Change</b>
    </td>
</tr>
</thead>
<? foreach ($diff as $attribute => $values)  {
   if (array_key_exists('diff', $values)) { 
   ?><tr>
    <td style="text-valign:top" class="aishead" valign="top"><b> <? echo $attribute ?></b></td>
    <td class="ais" ><pre><code>
  <? 
   
    echo str_replace( "\n", "<br/>",htmlentities($values['diff']));
    ?></code></pre>  
    </td>
    </tr>
<? }
  }
 ?>
</table>