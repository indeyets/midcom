<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$item =& $data['item'];
$product =& $item['product_obj'];
// If you need to muck this, muck it here (REMEMBER: same calculation is done in manage-cart-item)
$row_value = $product->price * $item['amount'];
$data['total_value'] += $row_value;
$product_url = $data['permalinks']->create_permalink($product->guid);
?>
        <tr class="item">
            <td class="title">
                <a href="&(product_url:h);" target="_blank">&(product->title);</a>
            </td>
            <td class="numeric"><?php echo round($item['amount'], 2); ?></td>
            <td class="numeric"><?php echo sprintf('%01.2f', round($row_value, 2)); ?></td>
        </tr>
