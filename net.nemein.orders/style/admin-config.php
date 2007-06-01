<?php

/*
$config =& $_MIDCOM->get_custom_context_data("configuration");
$l10n_midcom =& $_MIDCOM->get_custom_context_data("l10n_midcom");
$topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
*/

$l10n =& $_MIDCOM->get_custom_context_data("l10n");
$config_dm =& $_MIDCOM->get_custom_context_data("configuration_dm");

?>

<h2><?echo $l10n->get("configuration"); ?></h2>

<?php $config_dm->display_form(); ?>