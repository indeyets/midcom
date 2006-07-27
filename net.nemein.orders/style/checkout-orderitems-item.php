<?php

/*
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
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
*/

$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");
$order =& $GLOBALS["midcom"]->get_custom_context_data("order");
global $view_item;
$product = $view_item["product"]->data;
$currsign = " " . $config->get("currency_sign");
?>
<tr>
 <td>&(product["title"]);</td>
 <td style="text-align: right;">&(view_item["quantity"]);</td>
 <td style="text-align: right;">&(view_item["free_quantity"]);</td>
 <td style="text-align: right;">&(view_item["pay_quantity"]);</td>
 <td nowrap="nowrap" style="text-align: right;"><?php
  echo number_format($view_item["sum_net"], 2, ".", " "); ?>&(currsign:h);</td>
 <td nowrap="nowrap" style="text-align: right;"><?php
  echo number_format($view_item["vat"], 2, ".", " "); ?>&(currsign:h);</td>
 <td nowrap="nowrap" style="text-align: right;"><?php
  echo number_format($view_item["sum"], 2, ".", " "); ?>&(currsign:h);</td>
</tr>