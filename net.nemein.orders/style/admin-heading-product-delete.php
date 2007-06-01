<?php

/*
$config_dm =& $_MIDCOM->get_custom_context_data("configuration_dm");
$config =& $_MIDCOM->get_custom_context_data("configuration");
$topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
*/
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$l10n =& $_MIDCOM->get_custom_context_data("l10n");
$l10n_midcom =& $_MIDCOM->get_custom_context_data("l10n_midcom");
$product =& $_MIDCOM->get_custom_context_data("product");

?>

<h2><?echo $l10n->get("delete product"); ?></h2>

<p style="text-align: center; color: red; font-size: larger;"><strong><?echo 
$l10n->get("do you really want to delete this product?"); ?></strong></p>

<p style="text-align: center;"><a class="aisbutton" href="?ok=yes"><?echo 
$l10n_midcom->get("yes"); ?></a>&nbsp;<a class="aisbutton" 
href="&(prefix);product/view/<?echo $product->storage->id; ?>.html"><?echo 
$l10n_midcom->get("no"); ?></a></p>