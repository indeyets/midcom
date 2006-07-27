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
$order =& $GLOBALS["midcom"]->get_custom_context_data("order");
$totals = $order->get_totals();
*/

$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");
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