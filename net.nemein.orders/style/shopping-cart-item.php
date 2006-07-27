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
$order =& $GLOBALS["midcom"]->get_custom_context_data("order");
$cart =& $GLOBALS["midcom"]->get_custom_context_data("cart");
$items = $cart->get_cart();
*/

$site_prefix = $GLOBALS['midcom']->get_page_prefix();
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
global $view_product;
global $view;


$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");

$line = $l10n->get("%d copies, %s total, including VAT");
$line = sprintf($line, $view["quantity"],
                number_format($view["amount"],2) . " " . $config->get("currency_sign"));

$midgard = $GLOBALS["midcom"]->get_midgard();
$returnto = $midgard->uri;

?>

<li>
<a href="&(site_prefix);midcom-permalink-&(view_product['_storage_guid']);">&(view_product["title"]);:</a>
&(line:h);&nbsp;&nbsp;<?php
$code = urlencode($view['object']->id);
$returnto = urlencode($returnto);
$href = "{$prefix}process_cart.html?form_cart_submit=remove&form_code={$code}&form_action=remove&form_returnto={$returnto}";
$src = MIDCOM_STATIC_URL . '/stock-icons/16x16/trash.png';
$alt = $l10n->get("remove from cart");

?><a href="<?php echo $href; ?>"><img src="<?php echo $src; ?>" alt="&(alt);" title="&(alt);" /></a>
</li>
