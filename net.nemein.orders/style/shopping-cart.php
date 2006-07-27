<?php

/*
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$errstr =& $GLOBALS["midcom"]->get_custom_context_data("errstr");
$root_order_event =& $GLOBALS["midcom"]->get_custom_context_data("root_order_event");
$mailing_company_group =& $GLOBALS["midcom"]->get_custom_context_data("mailing_company_group");
$auth =& $GLOBALS["midcom"]->get_custom_context_data("auth");
$product =& $GLOBALS["midcom"]->get_custom_context_data("product");
$order =& $GLOBALS["midcom"]->get_custom_context_data("order");
*/

$cart =& $GLOBALS["midcom"]->get_custom_context_data("cart");
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