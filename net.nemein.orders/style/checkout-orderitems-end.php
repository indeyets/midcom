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
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$static = MIDCOM_STATIC_URL;
*/

$config =& $_MIDCOM->get_custom_context_data("configuration");
$l10n =& $_MIDCOM->get_custom_context_data("l10n");
$order =& $_MIDCOM->get_custom_context_data("order");
$totals = $order->get_totals();

if ($totals['shipping'])
{
    ?>
<tr>
 <td colspan="6" style="text-align: right;"><?echo $l10n->get("shipping:");?></td>
 <td nowrap="nowrap" style="border-top: 1px solid black; border-bottom: 2px solid black; text-align: right;"><?php
  echo number_format($totals["shipping"], 2, ".", " ") . " " . $config->get("currency_sign");
 ?></td>
</tr>
<?php } ?>

<tr>
 <td colspan="6" style="text-align: right;"><?echo $l10n->get("grand total:");?></td>
 <td nowrap="nowrap" style="border-top: 1px solid black; border-bottom: 2px solid black; text-align: right;"><?php
  echo number_format($totals["sum"], 2, ".", " ") . " " . $config->get("currency_sign");
 ?></td>
</tr>
</table>