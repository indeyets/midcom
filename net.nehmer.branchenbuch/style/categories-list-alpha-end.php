<?php
// The available request keys can be found in the components' API documentation
// of net_nehmer_account_handler_register
//
// Bind the view data, remember the reference assignment:
$data =& $_MIDCOM->get_custom_context_data('request_data');
$total = count ($data['category_list']);
?>
</ul>

<p><?php echo sprintf($data['l10n']->get('found %d categories.'), $total); ?></p>
<p><a href="&(data['return_url']);"><?php echo $data['l10n_midcom']->show('back'); ?></a></p>