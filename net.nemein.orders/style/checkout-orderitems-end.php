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
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$static = MIDCOM_STATIC_URL;
*/

$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");
$order =& $GLOBALS["midcom"]->get_custom_context_data("order");
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