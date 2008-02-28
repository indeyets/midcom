<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$item =& $data['item'];
$product =& $item['product_obj'];
// If you need to muck this, muck it here (REMEMBER: same calculation is done in manage-cart-item and view-cart-item)
$row_value = $product->price * $item['amount'];
$data['total_value'] += $row_value;
$product_url = $data['permalinks']->create_permalink($product->guid);
// Base indent
$idt = '        ';
// Output
echo "{$idt}<item>\n";
echo "{$idt}    <title>{$product->title}</title>\n";
echo "{$idt}    <guid>{$product->guid}</guid>\n";
echo "{$idt}    <units>{$item['amount']}</units>\n";
echo "{$idt}    <value>{$row_value}</value>\n";
echo "{$idt}    <link>{$product_url}</link>\n";
$mapper = new midcom_helper_xml_objectmapper();
echo $mapper->object2data($product, 'product_object');
echo "{$idt}</item>\n";
?>