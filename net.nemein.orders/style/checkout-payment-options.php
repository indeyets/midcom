<?php

/*
$config_dm =& $_MIDCOM->get_custom_context_data("configuration_dm");
$topic = $config_dm->data;
$topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$errstr =& $_MIDCOM->get_custom_context_data("errstr");
$root_order_event =& $_MIDCOM->get_custom_context_data("root_order_event");
$mailing_company_group =& $_MIDCOM->get_custom_context_data("mailing_company_group");
$auth =& $_MIDCOM->get_custom_context_data("auth");
$product =& $_MIDCOM->get_custom_context_data("product");
$l10n_midcom =& $_MIDCOM->get_custom_context_data("l10n_midcom");
$cart =& $_MIDCOM->get_custom_context_data("cart");
$items = $cart->get_cart();
$static = MIDCOM_STATIC_URL;
*/

// Remove the trailing slash from the host prefix
$host_prefix = substr($_MIDCOM->get_page_prefix(), 0, -1);
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$config =& $_MIDCOM->get_custom_context_data("configuration");
$l10n =& $_MIDCOM->get_custom_context_data("l10n");
$order =& $_MIDCOM->get_custom_context_data("order");
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
