<?php

/*
$topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$config =& $_MIDCOM->get_custom_context_data("configuration");
$topic = $config_dm->data;
$config_dm =& $_MIDCOM->get_custom_context_data("configuration_dm");
$l10n_midcom =& $_MIDCOM->get_custom_context_data("l10n_midcom");
$errstr =& $_MIDCOM->get_custom_context_data("errstr");
$root_order_event =& $_MIDCOM->get_custom_context_data("root_order_event");
$mailing_company_group =& $_MIDCOM->get_custom_context_data("mailing_company_group");
$auth =& $_MIDCOM->get_custom_context_data("auth");
$order =& $_MIDCOM->get_custom_context_data("order");
$product =& $_MIDCOM->get_custom_context_data("product");
$data = $product->datamanager->data;
*/

$l10n =& $_MIDCOM->get_custom_context_data("l10n");
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

$now = time();
$pre = "{$prefix}order/query_delivered.html?form_submit=a&form_mode=unix&form_start={$now}&form_end=";
$day = $pre . ($now - 86400); 
$week = $pre . ($now - 604800);
$month = $pre . ($now - 2678400);

?>

<h3><?echo $l10n->get("preset delivered order queries:"); ?></h3>

<ul>
<li><a href="&(day);"><?echo $l10n->get("last 24 hrs"); ?></a></li>
<li><a href="&(week);"><?echo $l10n->get("last week"); ?></a></li>
<li><a href="&(month);"><?echo $l10n->get("last month"); ?></a></li>
</ul>

<h3><?echo $l10n->get("custom delivered orders query:"); ?></h3>

<form action="&(prefix);order/query_delivered.html" method="post" enctype="multipart/form-data">
<p><?echo $l10n->get("date format: yyyy-mm-dd hh:mm");?></p>

<table border="0">
<tr>
 <td><?echo $l10n->get("start:");?></td'>
 <td><input name="form_start" value="" size="16" maxlength="16"></td>
</tr>
<tr>
 <td><?echo $l10n->get("end:");?></td'>
 <td><input name="form_end" value="" size="16" maxlength="16"></td>
</tr>
<tr>
 <td colspan="2">
  <input type="submit" name="form_submit" value="<?echo $l10n->get("execute query");?>">
  <input type="hidden" name="form_mode" value="string">
 </td>
</tr>
</table>
</form>