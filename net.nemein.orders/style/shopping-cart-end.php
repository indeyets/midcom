<?php

/*
$config_dm =& $_MIDCOM->get_custom_context_data("configuration_dm");
$topic = $config_dm->data;
$topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$config =& $_MIDCOM->get_custom_context_data("configuration");
$errstr =& $_MIDCOM->get_custom_context_data("errstr");
$root_order_event =& $_MIDCOM->get_custom_context_data("root_order_event");
$mailing_company_group =& $_MIDCOM->get_custom_context_data("mailing_company_group");
$auth =& $_MIDCOM->get_custom_context_data("auth");
$product =& $_MIDCOM->get_custom_context_data("product");
$l10n_midcom =& $_MIDCOM->get_custom_context_data("l10n_midcom");
$order =& $_MIDCOM->get_custom_context_data("order");
$cart =& $_MIDCOM->get_custom_context_data("cart");
$items = $cart->get_cart();
*/

$l10n =& $_MIDCOM->get_custom_context_data("l10n");
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

$midgard = $_MIDCOM->get_midgard();
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