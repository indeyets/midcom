<?php

/*
$config_dm =& $GLOBALS["midcom"]->get_custom_context_data("configuration_dm");
$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
*/
$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");

?>

<p><?echo $l10n->get("no corrupt reservations found."); ?></p>
