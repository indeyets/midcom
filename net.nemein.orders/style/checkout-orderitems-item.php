<?php

/*
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
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
*/

$config =& $_MIDCOM->get_custom_context_data("configuration");
$l10n =& $_MIDCOM->get_custom_context_data("l10n");
$order =& $_MIDCOM->get_custom_context_data("order");
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