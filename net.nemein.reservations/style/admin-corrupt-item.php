<?php

/*
$config_dm =& $GLOBALS["midcom"]->get_custom_context_data("configuration_dm");
$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$resource =& $GLOBALS["midcom"]->get_custom_context_data("resource");
*/

$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");
$l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");
$reservation =& $GLOBALS["midcom"]->get_custom_context_data("reservation");

$data = $reservation->dm->data;
$start = $data["start"];
$end = $data["end"];

?>
<li>
&(start["local_strfulldate"]); - &(end["local_strfulldate"]);: &(data["name"]);&nbsp;- 
<a href="&(prefix);reservation/view/&(data["_storage_id"]);.html"><?echo $l10n->get("view reservation"); 
?></a></li>