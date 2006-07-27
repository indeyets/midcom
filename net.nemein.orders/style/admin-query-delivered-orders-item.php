<?php

/*
$config_dm =& $GLOBALS["midcom"]->get_custom_context_data("configuration_dm");
$topic = $config_dm->data;
$topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$errstr =& $GLOBALS["midcom"]->get_custom_context_data("errstr");
$root_order_event =& $GLOBALS["midcom"]->get_custom_context_data("root_order_event");
$mailing_company_group =& $GLOBALS["midcom"]->get_custom_context_data("mailing_company_group");
$product =& $GLOBALS["midcom"]->get_custom_context_data("product");
$l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");
$cart =& $GLOBALS["midcom"]->get_custom_context_data("cart");
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");
$order =& $GLOBALS["midcom"]->get_custom_context_data("order");
$auth =& $GLOBALS["midcom"]->get_custom_context_data("auth");$items = $cart->get_cart();
*/

$toolbar =& $GLOBALS["midcom"]->get_custom_context_data("view_toolbar");

?>
<li style="border-top: 1px solid black; list-style: none; margin: 0; padding: 5px 0px 5px 0px;">
<?php 
  midcom_show_style("admin-show-order");
  echo $toolbar->render(); 
?>
</li>