<?php

/*
$topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$config =& $_MIDCOM->get_custom_context_data("configuration");
$topic = $config_dm->data;
$config_dm =& $_MIDCOM->get_custom_context_data("configuration_dm");
$l10n_midcom =& $_MIDCOM->get_custom_context_data("l10n_midcom");
$errstr =& $_MIDCOM->get_custom_context_data("errstr");
$root_order_event =& $_MIDCOM->get_custom_context_data("root_order_event");
$mailing_company_group =& $_MIDCOM->get_custom_context_data("mailing_company_group");
$auth =& $_MIDCOM->get_custom_context_data("auth");
$order =& $_MIDCOM->get_custom_context_data("order");
$product =& $_MIDCOM->get_custom_context_data("product");
$data = $product->datamanager->data;
*/

$l10n =& $_MIDCOM->get_custom_context_data("l10n");
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

?>

<h1><?echo $l10n->get("checkout: complete"); ?></h1>

<p><?echo $l10n->get("received and processed message"); ?></p>

<p><a href="&(prefix);"><?echo $l10n->get("back to product overview"); ?></a></p>