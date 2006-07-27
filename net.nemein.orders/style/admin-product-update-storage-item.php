<?php

/*
$config_dm =& $GLOBALS["midcom"]->get_custom_context_data("configuration_dm");
$config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
$l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");
$l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");
$topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
*/

$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$product =& $GLOBALS["midcom"]->get_custom_context_data("product");
$data = $product->datamanager->data;

if ($data['stockmin'] != -1)
{
    $stockmin = $data['stockmin']; 
    if ($data['available'] <= $data['stockmin'])
	{
	    $style = " style='color: red;'";
	}
	else
	{
	    $style = '';
	}
}
else
{
    $stockmin = '&nbsp;';
    $style = '';
}
?>

<tr>
    <td&(style);><a href="&(prefix);product/view/&(data['_storage_id']);.html">&(data['code']);</a></td>
    <td&(style);>&(data['title']);</td>
    <td&(style);>&(stockmin:h);</td>
    <td&(style);>&(data['available']);</td>
    <td&(style);>&(data['instock']);</td>
    <td&(style);><input name="form_&(data['_storage_id']);" type="text" size="6" maxlength="6" value="0"/></td>
</tr>