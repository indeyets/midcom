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
$order =& $_MIDCOM->get_custom_context_data("order");
$totals = $order->get_totals();
*/

$config =& $_MIDCOM->get_custom_context_data("configuration");
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$l10n =& $_MIDCOM->get_custom_context_data("l10n");
$static = MIDCOM_STATIC_URL;


if ($config->get('enable_net_nemein_payment_integration'))
{
?>
<p>
<a href="&(prefix);checkout/payment.html"><img align="middle" src="&(static);/stock-icons/16x16/approved.png" /> <?echo $l10n->get('yes, i want to order these product');?></a>
<a href="&(prefix);checkout/confirm.html?form_order_cancel=ok"><img align="middle" src="&(static);/stock-icons/16x16/cancel.png" /> <?echo $l10n->get('no, i changed my mind');?></a>
</p>
<?php
}
else
{
?>
<p>
<a href="&(prefix);checkout/confirm.html?form_order_confirm=ok"><img align="middle" src="&(static);/stock-icons/16x16/approved.png" /> <?echo $l10n->get('yes, i want to order these product');?></a>
<a href="&(prefix);checkout/confirm.html?form_order_cancel=ok"><img align="middle" src="&(static);/stock-icons/16x16/cancel.png" /> <?echo $l10n->get('no, i changed my mind');?></a>
</p>
<?php } ?>