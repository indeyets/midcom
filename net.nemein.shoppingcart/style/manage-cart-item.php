<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$item =& $data['item'];
$product =& $item['product_obj'];
// If you need to muck this, muck it here (REMEMBER: same calculation is done in view-cart-item and vie-ajax-cart-item)
$row_value = $product->price * $item['amount'];
$data['total_value'] += $row_value;
$product_url = $data['permalinks']->create_permalink($product->guid);
?>
            <tr class="item">
                <td class="title">
                    <a href="&(product_url:h);" target="_blank">&(product->title);</a>
                </td>
                <td class="numeric"><input type="text" size="2" id="net_nemein_shoppingcart_&(product->guid);" name="net_nemein_shoppingcart_managecart_amount[&(product->guid);]" value="<?php echo round($item['amount'], 2); ?>" /></td>
                <td class="numeric"><?php echo sprintf('%01.2f', round($row_value, 2)); ?></td>
                <td>
                    <button class="net_nemein_shoppingcart_manage_delete" onclick="javascript:$('net_nemein_shoppingcart_&(product->guid);').value=0;$('net_nemein_shoppingcart_manage_update').click();">
                        <img src="<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/16x16/trash.png" />
                    </button>
                </td>
            </tr>
