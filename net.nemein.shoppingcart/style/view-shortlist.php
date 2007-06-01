<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$items_str = sprintf($data['l10n']->get('there are %u items in the cart'), round($data['items_count'],2));
if ($_MIDCOM->skip_page_style)
{
    midcom_show_style('view-shortlist-html_header');
}
?>
<div class="net_nemein_shoppingcart_cart_shortlist">&(items_str:h);
<?php
$data['list-cart-actions_target'] = '_parent';
$data['list-cart-actions_append_class'] = 'shortlist';
midcom_show_style('list-cart-actions');
unset($data['list-cart-actions_target'], $data['list-cart-actions_append_class'])
?>
</p>
<?php
if ($_MIDCOM->skip_page_style)
{
    midcom_show_style('view-shortlist-html_footer');
}
?>