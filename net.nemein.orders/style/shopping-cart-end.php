<?php

/*
$config_dm =& $GLOBALS["midcom"]->get_custom_context_data("configuration_dm");
$topic = $config_dm->data;
$topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$errstr =& $GLOBALS["midcom"]->get_custom_context_data("errstr");
$root_order_event =& $GLOBALS["midcom"]->get_custom_context_data("root_order_event");
$mailing_company_group =& $GLOBALS["midcom"]->get_custom_context_data("mailing_company_group");
$auth =& $GLOBALS["midcom"]->get_custom_context_data("auth");
$product =& $GLOBALS["midcom"]->get_custom_context_data("product");
$l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");
$order =& $GLOBALS["midcom"]->get_custom_context_data("order");
$cart =& $GLOBALS["midcom"]->get_custom_context_data("cart");
$items = $cart->get_cart();
*/

$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

$midgard = $GLOBALS["midcom"]->get_midgard();
$returnto = $midgard->uri;

?>

<li>
<?php
$returnto = urlencode($returnto);
$href = "{$prefix}process_cart.html?form_cart_submit=remove_all&form_action=remove_all&form_returnto={$returnto}";
$src = MIDCOM_STATIC_URL . '/stock-icons/16x16/trash.png';
$alt = $l10n->get("remove all from cart");
?><a href="<?php echo $href; ?>">&(alt); <img src="<?php echo $src; ?>" alt="&(alt);" title="&(alt);" /></a>
</li>

<li><a href="&(prefix);checkout/address.html"><?echo $l10n->get("checkout");?></a></li>

</ul>

<p style="font-size: smaller;"><?echo $l10n->get("cart is without free items notice"); ?></p>