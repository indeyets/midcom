<?php

/*
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
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

?>

<table border="0" cellspacing="0" cellpadding="5">
<tr>
 <th><?echo $l10n->get("product title");?></th>
 <th><?echo $l10n->get("quantity");?></th>
 <th><?echo $l10n->get("free copies");?></th>
 <th><?echo $l10n->get("billable copies");?></th>
 <th><?echo $l10n->get("net sum");?></th>
 <th><?echo $l10n->get("vat");?></th>
 <th><?echo $l10n->get("gross sum");?></th>
</tr>