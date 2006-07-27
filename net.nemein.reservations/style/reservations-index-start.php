<?php

/*
$config_dm =& $GLOBALS["midcom"]->get_custom_context_data("configuration_dm");
$topic = $config_dm->data;
$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");
$errstr =& $GLOBALS["midcom"]->get_custom_context_data("errstr");
$auth =& $GLOBALS["midcom"]->get_custom_context_data("auth");
$reservation =& $GLOBALS["midcom"]->get_custom_context_data("reservation");
$topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
*/
$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$resource =& $GLOBALS["midcom"]->get_custom_context_data("resource");
$guid = $resource->dm->data["_storage_guid"];
?>
<ul>
<li><a href="&(prefix);&(guid);/res_start.html"><?echo $l10n->get("create new reservation"); ?></a></li>
