<?php

/*
$topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$topic = $config_dm->data;
$config_dm =& $GLOBALS["midcom"]->get_custom_context_data("configuration_dm");
$l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");
$errstr =& $GLOBALS["midcom"]->get_custom_context_data("errstr");
$root_order_event =& $GLOBALS["midcom"]->get_custom_context_data("root_order_event");
$mailing_company_group =& $GLOBALS["midcom"]->get_custom_context_data("mailing_company_group");
$auth =& $GLOBALS["midcom"]->get_custom_context_data("auth");
$order =& $GLOBALS["midcom"]->get_custom_context_data("order");
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$product =& $GLOBALS["midcom"]->get_custom_context_data("product");
$data = $product->datamanager->data;
*/

$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");

?>

<h1><?echo $l10n->get("checkout: confirm order"); ?></h1>