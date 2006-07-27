<?php

/*
$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");
$topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
*/

$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");
$config_dm =& $GLOBALS["midcom"]->get_custom_context_data("configuration_dm");

?>

<?php midcom_show_style("admin-heading-toolbar"); ?>

<h2><?echo $l10n->get("configuration"); ?></h2>

<?php $config_dm->display_form(); ?>