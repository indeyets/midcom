<?php

/*
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
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