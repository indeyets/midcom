<?php

/*
$config_dm =& $_MIDCOM->get_custom_context_data("configuration_dm");
$config =& $_MIDCOM->get_custom_context_data("configuration");
$l10n =& $_MIDCOM->get_custom_context_data("l10n");
$l10n_midcom =& $_MIDCOM->get_custom_context_data("l10n_midcom");
$topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
*/

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$product =& $_MIDCOM->get_custom_context_data("product");
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