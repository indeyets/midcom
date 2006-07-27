<?php

/*
$config_dm =& $GLOBALS["midcom"]->get_custom_context_data("configuration_dm");
$topic = $config_dm->data;
$topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$errstr =& $GLOBALS["midcom"]->get_custom_context_data("errstr");
$root_order_event =& $GLOBALS["midcom"]->get_custom_context_data("root_order_event");
$mailing_company_group =& $GLOBALS["midcom"]->get_custom_context_data("mailing_company_group");
$auth =& $GLOBALS["midcom"]->get_custom_context_data("auth");
$product =& $GLOBALS["midcom"]->get_custom_context_data("product");
$l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");
$cart =& $GLOBALS["midcom"]->get_custom_context_data("cart");
$items = $cart->get_cart();
$static = MIDCOM_STATIC_URL;
*/

// Remove the trailing slash from the host prefix
$host_prefix = substr($GLOBALS['midcom']->get_page_prefix(), 0, -1);
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");
$order =& $GLOBALS["midcom"]->get_custom_context_data("order");
$totals = $order->get_totals();

/*
Array
(
    [sum_net] => 35
    [vat] => 5
    [sum] => 40
)
*/

$return = "{$host_prefix}{$prefix}checkout/process_payment.html?order_id={$order->data['_storage_id']}";

$payment =& net_nemein_payment_factory::get_instance();
$payment->render_payment_links($totals['sum'], $return, $order->data['_storage_id']);
?>
