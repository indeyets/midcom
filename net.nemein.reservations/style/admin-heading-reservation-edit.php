<?php

/*
$config_dm =& $GLOBALS["midcom"]->get_custom_context_data("configuration_dm");
$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");
$topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$reservation =& $GLOBALS["midcom"]->get_custom_context_data("reservation");
*/

$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");
$resource =& $GLOBALS["midcom"]->get_custom_context_data("resource");
$title = sprintf($l10n->get("edit reservation for %s:"), $resource->dm->data["name"]);

?>
<h2>&(title);</h2>
