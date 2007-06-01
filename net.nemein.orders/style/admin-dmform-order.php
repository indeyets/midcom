<?php

/*
$config_dm =& $_MIDCOM->get_custom_context_data("configuration_dm");
$config =& $_MIDCOM->get_custom_context_data("configuration");
$l10n =& $_MIDCOM->get_custom_context_data("l10n");
$l10n_midcom =& $_MIDCOM->get_custom_context_data("l10n_midcom");
$topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$product =& $_MIDCOM->get_custom_context_data("product");
*/
$order=& $_MIDCOM->get_custom_context_data("order");
$order->datamanager->display_form();

?>