<?php
// The available request keys can be found in the components' API documentation
// of net_nehmer_account_handler_register
//
// Bind the view data, remember the reference assignment:
$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2><?php echo $data['l10n']->get('add entry') . ": {$data['type']->name}: " . $data['l10n']->get('enty published'); ?></h2>

<p><?php $data['l10n']->show('entry published text'); ?></p>

<p><a href='&(prefix);entry/list/self.html'><?php $data['l10n_midcom']->show('back'); ?></a></p>
