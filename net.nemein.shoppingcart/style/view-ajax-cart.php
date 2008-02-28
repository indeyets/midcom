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


/*
midcom_show_style('view-cart-header');
foreach ($data['items'] as $item)
{
    $data['item'] =& $item;
    midcom_show_style('view-cart-item');
    unset($data['item']);
}
midcom_show_style('view-cart-totals');
midcom_show_style('view-cart-footer');
*/



?>