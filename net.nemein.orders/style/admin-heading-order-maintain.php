<?php

/*
$config_dm =& $_MIDCOM->get_custom_context_data("configuration_dm");
$config =& $_MIDCOM->get_custom_context_data("configuration");
$topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$l10n_midcom =& $_MIDCOM->get_custom_context_data("l10n_midcom");
*/
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$l10n =& $_MIDCOM->get_custom_context_data("l10n");

?>

<h2><?echo $l10n->get("incomplete/corrupt orders"); ?></h2>

<p><a href="&(prefix);order/maintain.html?form_mode=delete_incomplete&form_age=3600"><?echo $l10n->get("delete all incomplete orders older then one hour"); ?></a></p>

<h2><?echo $l10n->get("corrupt orders"); ?></h2>