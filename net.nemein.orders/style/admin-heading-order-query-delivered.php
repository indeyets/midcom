<?php

/*
$config_dm =& $_MIDCOM->get_custom_context_data("configuration_dm");
$config =& $_MIDCOM->get_custom_context_data("configuration");
$topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
*/
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$l10n =& $_MIDCOM->get_custom_context_data("l10n");
$l10n_midcom =& $_MIDCOM->get_custom_context_data("l10n_midcom");

?>

<h2><?echo $l10n->get("query delivered orders"); ?></h2>

