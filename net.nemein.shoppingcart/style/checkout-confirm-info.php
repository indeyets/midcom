<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1>&(data['title']);</h1>

<div class="net_nemein_shopping_checkout">
    <h2><?php echo $data['l10n']->get('review cart'); ?></h2>
    <div class="cartcontainer">
        <table class="net_nemein_shoppingcart">
            <thead>
                <tr>
                    <th><?php echo $data['l10n']->get('item'); ?></th>
                    <th><?php echo $data['l10n']->get('units'); ?></th>
                    <th><?php echo $data['l10n']->get('price'); ?></th>
                </tr>
            </thead>
            <tbody>
<?php
foreach ($data['items'] as $item)
{
    $data['item'] =& $item;
    midcom_show_style('view-cart-item');
    unset($data['item']);
}
midcom_show_style('view-cart-totals');
?>
        </table>
    </div>

    <h2><?php echo $data['l10n']->get('contact information'); ?></h2>
    <?php $data['controller']->display_form(); ?>
</div>
