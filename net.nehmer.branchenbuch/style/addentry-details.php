<?php
// The available request keys can be found in the components' API documentation
// of net_nehmer_account_handler_register
//
// Bind the view data, remember the reference assignment:
$data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<h2><?php echo $data['l10n']->get('add entry') . ": {$data['type']->name}: " . $data['l10n']->get('verify details'); ?></h2>

<h2><?php $data['controller']->display_form(); ?></h2>