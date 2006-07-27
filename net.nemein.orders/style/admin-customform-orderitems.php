<?php

/*
$config_dm =& $GLOBALS["midcom"]->get_custom_context_data("configuration_dm");
$topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$product =& $GLOBALS["midcom"]->get_custom_context_data("product");
*/

$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");
$l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");

$order=& $GLOBALS["midcom"]->get_custom_context_data("order");
$items = $order->get_order();
$currsign = $config->get("currency_sign");

$order->datamanager->display_view();

?>
<form action="" enctype="multipart/form-data" method="POST">

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
 <td style="text-align: right;">
  <input type="text" name="&(guid);" value="&(item["quantity"]);" size="4" maxlength="4">
 </td>
 <td style="text-align: right;">&(item["free_quantity"]);</td>
 <td nowrap="nowrap" style="text-align: right;"><?php
  echo number_format($item["sum_net"], 2, ".", " "); ?>&(currsign:h);</td>
 <td nowrap="nowrap" style="text-align: right;"><?php
  echo number_format($item["vat"], 2, ".", " "); ?>&(currsign:h);</td>
 <td nowrap="nowrap" style="text-align: right;"><?php
  echo number_format($item["sum"], 2, ".", " "); ?>&(currsign:h);</td>
</tr>
<?php
    }
?> 
</table>

<div class="form_toolbar">
 <input type="submit" name="form_submit" value="<?php echo $l10n_midcom->get("save"); ?>">
 <input type="submit" name="form_cancel" value="<?php echo $l10n_midcom->get("cancel"); ?>">
 <input type="reset" value="<?php echo $l10n_midcom->get("reset"); ?>">
</div>

</form>