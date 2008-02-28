<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
if (!isset($data['total_value']))
{
    $data['total_value'] = 0;
}
if (!isset($data['permalinks']))
{
    $data['permalinks'] = new midcom_services_permalinks();
}
$item_count = count($data['items']);

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
echo "<net_nemein_shoppingcart>\n";
echo "    <item_count_rows>{$item_count}</item_count_rows>\n";
echo "    <item_count_units>{$data['items_count']}</item_count_units>\n";
if ($item_count == 0)
{
    echo "    <items />\n";
}
else
{
    echo "    <items>\n";
    foreach ($data['items'] as $item)
    {
        $data['item'] =& $item;
        midcom_show_style('view-ajax-cart-item');
        unset($data['item']);
    }
    echo "    </items>\n";
}
echo "    <total_value>{$data['total_value']}</total_value>\n";
echo "</net_nemein_shoppingcart>\n";
?>
<!--
#  Cart manipulation cheatsheet

## Adding items

POST an array of product GUIDs as 'net_nemein_shoppingcart_managecart_additems' 
to /path/to/component/xml/manage/, if the product is already in cart 
the number of units is incemented.

Alternative to the array of GUIDs you can POST a single GUID value.

## Managing item numbers (or units)

POST array 'net_nemein_shoppingcart_managecart_amount' to /path/to/component/xml/manage/
array is keyed by product GUID and values are new number of units for the product.

If you post an amount for a product that is not already in cart it's silently ignored

## Deleting items

Set number of item units to zero as outlined above

-->
