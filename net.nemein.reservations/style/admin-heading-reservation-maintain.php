<?php

/*
$config_dm =& $GLOBALS["midcom"]->get_custom_context_data("configuration_dm");
$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");
*/
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");

?>

<h2><?echo $l10n->get("incomplete/corrupt reservations"); ?></h2>

<p><a href="&(prefix);reservation/maintain.html?form_mode=delete_incomplete&form_age=3600"><?echo $l10n->get("delete all incomplete reservations older then one hour"); ?></a></p>

<h2><?echo $l10n->get("corrupt reservations"); ?></h2>