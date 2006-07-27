<?php

/*
$topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$topic = $config_dm->data;
$config_dm =& $GLOBALS["midcom"]->get_custom_context_data("configuration_dm");
$l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");
$errstr =& $GLOBALS["midcom"]->get_custom_context_data("errstr");
$root_order_event =& $GLOBALS["midcom"]->get_custom_context_data("root_order_event");
$mailing_company_group =& $GLOBALS["midcom"]->get_custom_context_data("mailing_company_group");
$auth =& $GLOBALS["midcom"]->get_custom_context_data("auth");
$order =& $GLOBALS["midcom"]->get_custom_context_data("order");
*/

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

<li style="padding: 5px 0 5px 0; border-top: 1px solid black; clear: both;">

<?php if (! is_null($image)) { ?>
<a href="&(image['url']);"><img style="border: none; margin: 5px;" src="&(thumb['url']);" align="left" alt="&(desc);" title="&(desc);" &(thumb['size_line']:h);></a>
<?php } ?>

<strong>&(data["title"]);</strong><br>
&(data["abstract"]);<br>

<?php
if ($data["status"] == "online") {
?>
<a href="&(prefix);pub/&(data['code']);.html"><?echo $l10n->get("product details");?></a>
<?php
} else if ($data["status"] == "outside") {
?>
<a href="&(data['outsideurl']);" target="_blank">&(data['outsideurllabel']);</a>
<?php
} else {
    echo $l10n->get("product cannot be ordered");
}
?>

</li>