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
$order =& $_MIDCOM->get_custom_context_data("order");
$cart =& $_MIDCOM->get_custom_context_data("cart");
$items = $cart->get_cart();
*/

$site_prefix = $_MIDCOM->get_page_prefix();
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$config =& $_MIDCOM->get_custom_context_data("configuration");
global $view_product;
global $view;


$l10n =& $_MIDCOM->get_custom_context_data("l10n");

$line = $l10n->get("%d copies, %s total, including VAT");
$line = sprintf($line, $view["quantity"],
                number_format($view["amount"],2) . " " . $config->get("currency_sign"));

$midgard = $_MIDCOM->get_midgard();
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
