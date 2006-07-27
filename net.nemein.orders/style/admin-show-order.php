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
$items = $order->get_order();
$totals = $order->get_totals();
$currsign = $config->get("currency_sign");

$order->datamanager->display_view();

if (count($items) == 0) {
    echo "<p>" . $l10n->get("order empty.") . "</p>\n";
} else {
?>
<table border="0" cellspacing="0" cellpadding="5" style="margin: 0; padding: 0;">
<tr>
 <th><?echo $l10n->get("code");?></th>
 <th><?echo $l10n->get("product title");?></th>
 <th><?echo $l10n->get("quantity");?></th>
 <th><?echo $l10n->get("free copies");?></th>
 <th><?echo $l10n->get("net sum");?></th>
 <th><?echo $l10n->get("vat");?></th>
 <th><?echo $l10n->get("gross sum");?></th>
</tr>
<?php
    foreach ($items as $guid => $item) {
        $product = $item["product"]->data;
?>
<tr>
 <td>&(product["code"]);</td>
 <td>&(product["title"]);</td>
 <td style="text-align: right;">&(item["quantity"]);</td>
 <td style="text-align: right;">&(item["free_quantity"]);</td>
 <td nowrap="nowrap" style="text-align: right;"><?php
  echo number_format($item["sum_net"], 2, ".", " "); ?>&(currsign:h);</td>
 <td nowrap="nowrap" style="text-align: right;"><?php
  echo number_format($item["vat"], 2, ".", " "); ?>&(currsign:h);</td>
 <td nowrap="nowrap" style="text-align: right;"><?php
  echo number_format($item["sum"], 2, ".", " "); ?>&(currsign:h);</td>
</tr>
<?php } if ($totals['shipping']) { ?>
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
<?php
}
?>