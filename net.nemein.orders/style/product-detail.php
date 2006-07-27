<?php

/*
$topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$topic = $config_dm->data;
$config_dm =& $GLOBALS["midcom"]->get_custom_context_data("configuration_dm");
$l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");
$errstr =& $GLOBALS["midcom"]->get_custom_context_data("errstr");
$root_order_event =& $GLOBALS["midcom"]->get_custom_context_data("root_order_event");
$mailing_company_group =& $GLOBALS["midcom"]->get_custom_context_data("mailing_company_group");
$auth =& $GLOBALS["midcom"]->get_custom_context_data("auth");
$order =& $GLOBALS["midcom"]->get_custom_context_data("order");
*/

$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$product =& $GLOBALS["midcom"]->get_custom_context_data("product");
$data = $product->datamanager->data;
$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");

if (! is_null($data["image"]["thumbnail"])) { 
    $image = $data["image"];
    $thumb = $image["thumbnail"];
    $desc = trim ($image["description"] . " ") 
            . "(" . $image["size_x"]
            . "x" . $image["size_y"]
            . ", " . $image["formattedsize"] . "Bytes)";
} else {
    $image = null;
}

?>

<?php if (! is_null($image)) { ?>
<a href="&(image['url']);"><img style="border: none; margin: 5px;" src="&(thumb['url']);" align="left" alt="&(desc);" title="&(desc);" &(thumb['size_line']:h);></a>
<?php } ?>

<div>
&(data["description"]:h);
</div>

<table border="0">
<tr><td><?echo $l10n->get("price"); ?>:</td><td><?php
    echo number_format($data['price'], 2)?>&nbsp;<?php
    echo $config->get("currency_sign"); ?></td></tr>
<tr><td><?echo $l10n->get("vat"); ?>:</td><td><?php
    echo number_format($data['vat'], 2)?>&nbsp;%</td></tr>
<?php if ($data['maxfreecopies'] > 0) { ?>
<tr><td><?echo $l10n->get("maxfreecopies"); ?>:</td><td>&(data["maxfreecopies"]);</td></tr>
<?php 
}
if ($data['maxperorder'] > 0) { ?>
<tr><td><?echo $l10n->get("maxperorder"); ?>:</td><td>&(data["maxperorder"]);</td></tr>
<?php } ?>
<tr><td><?echo $l10n->get("available"); ?>:</td><td>&(data["available"]);</td></tr>
</table>

<p style="clear: both;"><a href="&(prefix);"><?echo $l10n->get("back to product overview"); ?></a></p>