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
midcom_show_style('view-cart-header');
foreach ($data['items'] as $item)
{
    $data['item'] =& $item;
    midcom_show_style('view-cart-item');
    unset($data['item']);
}
midcom_show_style('view-cart-totals');
midcom_show_style('view-cart-footer');
?>