<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$items =& $data['items'];
$strlen = 'strlen';
$str_pad = 'str_pad';
if (function_exists('mb_strlen'))
{
    $strlen = 'mb_strlen';
    $str_pad = 'mb_str_pad';
    mb_internal_encoding('UTF-8');
}

$total_value = 0;

$items_rendered = array
(
    'code' => array('values' => array(), 'maxlen' => $strlen($data['l10n']->get('code'))),
    'item' => array('values' => array(), 'maxlen' => $strlen($data['l10n']->get('item'))),
    'units' => array('values' => array(), 'maxlen' => $strlen($data['l10n']->get('units'))),
    'price' => array('values' => array(), 'maxlen' => $strlen($data['l10n']->get('price'))),
);
// Render values to array
foreach ($items as $key => $item)
{
    $product =& $item['product_obj'];
    $row_value = $product->price * $item['amount'];
    $total_value += $row_value;

    $items_rendered['code']['values'][$key] = $product->code;
    if ($strlen($product->code) > $items_rendered['code']['maxlen'])
    {
        $items_rendered['code']['maxlen'] = $strlen($product->code);
    }

    $items_rendered['item']['values'][$key] = $product->title;
    if ($strlen($product->title) > $items_rendered['item']['maxlen'])
    {
        $items_rendered['item']['maxlen'] = $strlen($product->title);
    }

    $units_rendered = round($item['amount'], 2);
    $items_rendered['units']['values'][$key] = $units_rendered;
    if ($strlen($units_rendered) > $items_rendered['units']['maxlen'])
    {
        $items_rendered['units']['maxlen'] = $strlen($units_rendered);
    }

    $price_rendered = sprintf('%01.2f', round($row_value, 2));
    $items_rendered['price']['values'][$key] = $price_rendered;
    if ($strlen($price_rendered) > $items_rendered['price']['maxlen'])
    {
        $items_rendered['price']['maxlen'] = $strlen($price_rendered);
    }
}
$total_value_rendered = sprintf('%01.2f', round($total_value, 2));
if ($strlen($total_value_rendered) > $items_rendered['price']['maxlen'])
{
    $items_rendered['price']['maxlen'] = $strlen($total_value_rendered);
}

// Render table heading
$columns_header = '| ';
foreach ($items_rendered as $column => $info)
{
    $columns_header .= $str_pad($data['l10n']->get($column), $info['maxlen'], ' ', STR_PAD_BOTH) . ' | ';
}
$columns_header = trim($columns_header);
$spacer_width = $strlen($columns_header);
$columns_header .= "\n";
$d = $spacer_width;
$spacer_str = '';
while ($d--)
{
    $spacer_str .= '-';
}
echo $columns_header . "{$spacer_str}\n";
// Output rendered item values
foreach ($items as $key => $item)
{
    $item_row = '| ';
    foreach ($items_rendered as $column => $info)
    {
        $pad_type = STR_PAD_LEFT;
        if ($column == 'item')
        {
            $pad_type = STR_PAD_RIGHT;
        }
        $value =& $info['values'][$key];
        $item_row .= $str_pad($value, $info['maxlen'], ' ', $pad_type) . ' | ';
    }
    $item_row = trim($item_row);
    echo $item_row . "\n";
}
echo $spacer_str . "\n";
echo '  ' . $data['l10n']->get('total');
$d = $spacer_width - (count($items_rendered['price']['maxlen']) * 5) - $items_rendered['price']['maxlen'] - $strlen($data['l10n']->get('total')) - 2;
while ($d--)
{
    echo ' ';
}
echo " | " . $str_pad($total_value_rendered, $items_rendered['price']['maxlen'], ' ', STR_PAD_LEFT) . " |\n";

?>