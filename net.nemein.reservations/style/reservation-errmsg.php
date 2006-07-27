<?php

/*
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$config_dm =& $GLOBALS["midcom"]->get_custom_context_data("configuration_dm");
$topic = $config_dm->data;
$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");
$l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");
$errstr =& $GLOBALS["midcom"]->get_custom_context_data("errstr");
$auth =& $GLOBALS["midcom"]->get_custom_context_data("auth");
$reservation =& $GLOBALS["midcom"]->get_custom_context_data("reservation");
$topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$resource =& $GLOBALS["midcom"]->get_custom_context_data("resource");
$resource->dm->display_view();
*/

$session = new midcom_service_session();

if ($session->exists("reservation_errmsg")) {
?>
<div style="background-color: white; border: 1px solid red; padding: 5px;">
<?echo $session->remove("reservation_errmsg"); ?> 
</div>
<?php } ?>