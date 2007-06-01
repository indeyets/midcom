<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$checkout_str = $data['l10n']->get('proceed to checkout');
$edit_str = $data['l10n']->get('edit cart');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<ul class="net_nemein_shoppingcart_cart_actions<?php if (isset($data['list-cart-actions_append_class'])) { echo " {$data['list-cart-actions_append_class']}"; } ?>">
    <li class="edit_cart"><a href="&(prefix:s);"<?php if (isset($data['list-cart-actions_target'])) { echo " target=\"{$data['list-cart-actions_target']}\""; } ?>>&(edit_str:h);</a></li>
    <li class="checkout"><a href="&(prefix:s);checkout/"<?php if (isset($data['list-cart-actions_target'])) { echo " target=\"{$data['list-cart-actions_target']}\""; } ?>>&(checkout_str:h);</a></li>
</ul>
