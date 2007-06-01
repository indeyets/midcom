<?php

/*
$config_dm =& $_MIDCOM->get_custom_context_data("configuration_dm");
$topic = $config_dm->data;
$topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$config =& $_MIDCOM->get_custom_context_data("configuration");
$errstr =& $_MIDCOM->get_custom_context_data("errstr");
$root_order_event =& $_MIDCOM->get_custom_context_data("root_order_event");
$mailing_company_group =& $_MIDCOM->get_custom_context_data("mailing_company_group");
$product =& $_MIDCOM->get_custom_context_data("product");
$l10n_midcom =& $_MIDCOM->get_custom_context_data("l10n_midcom");
$cart =& $_MIDCOM->get_custom_context_data("cart");
$items = $cart->get_cart();
*/

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$l10n =& $_MIDCOM->get_custom_context_data("l10n");
$order =& $_MIDCOM->get_custom_context_data("order");
$auth =& $_MIDCOM->get_custom_context_data("auth");
$toolbar =& $_MIDCOM->get_custom_context_data("view_toolbar");

?>
<li style="border-top: 1px solid black; list-style: none; margin: 0; padding: 5px 0px 5px 0px;">
<?php 
midcom_show_style("admin-show-order"); 
echo $toolbar->render();
?>
</li>