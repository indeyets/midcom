<?php

/*
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$config =& $_MIDCOM->get_custom_context_data("configuration");
$errstr =& $_MIDCOM->get_custom_context_data("errstr");
$root_order_event =& $_MIDCOM->get_custom_context_data("root_order_event");
$mailing_company_group =& $_MIDCOM->get_custom_context_data("mailing_company_group");
$auth =& $_MIDCOM->get_custom_context_data("auth");
$product =& $_MIDCOM->get_custom_context_data("product");
$order =& $_MIDCOM->get_custom_context_data("order");
*/

$cart =& $_MIDCOM->get_custom_context_data("cart");
$items = $cart->get_cart();

if (count($items) > 0)
{    
    midcom_show_style("heading-shopping-cart");
    
    midcom_show_style("shopping-cart-begin");
    foreach ($items as $guid => $item) {
        $GLOBALS["view_product"] =& $item["product"]->data;
        $GLOBALS["view"] =& $item;
        midcom_show_style("shopping-cart-item");
    }
    midcom_show_style("shopping-cart-end");
}


?>