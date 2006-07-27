<?php

/*
$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$topic = $config_dm->data;
$config_dm =& $GLOBALS["midcom"]->get_custom_context_data("configuration_dm");
$l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");
$errstr =& $GLOBALS["midcom"]->get_custom_context_data("errstr");
$root_order_event =& $GLOBALS["midcom"]->get_custom_context_data("root_order_event");
$mailing_company_group =& $GLOBALS["midcom"]->get_custom_context_data("mailing_company_group");
$auth =& $GLOBALS["midcom"]->get_custom_context_data("auth");
$order =& $GLOBALS["midcom"]->get_custom_context_data("order");
*/

$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$product =& $GLOBALS["midcom"]->get_custom_context_data("product");
$data = $product->datamanager->data;
$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");

$midgard = $GLOBALS["midcom"]->get_midgard();
$returnto = $midgard->uri;

if ($data['available'] > 0)
{
?>
<form action="&(prefix);process_cart.html" method="post" enctype="multipart/form-data">
<p><?php echo sprintf($l10n->get("order %s copies of this product"),
                  '<input name="form_count" value="1" size="4" maxlength="4">');
?> <input type="submit" name="form_cart_submit" value="<?echo $l10n->get("add to cart");?>">
<input type="hidden" name="form_code" value="&(data['_storage_id']);">
<input type="hidden" name="form_action" value="add">
<input type="hidden" name="form_returnto" value="&(returnto);">
</form>
<?php
}
else
{
?>
<p><?php echo $l10n->get('cannot order, no items available');?></p>
<?php
}