<?php
// The available request keys can be found in the components' API documentation
// of net_nehmer_account_handler_register
//
// Bind the view data, remember the reference assignment:
$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
</ul>

<p><?php echo sprintf($data['l10n']->get('found %d entries.'), $data['total']); ?></p>
<p><a href="&(data['return_url']);"><?php $data['l10n_midcom']->show('back'); ?></a></p>

<p>
<?php if ($data['previous_page'] !== null) { ?>
<a href="&(data['previous_page_url']);"><?php $data['l10n_midcom']->show('previous page'); ?></a>&nbsp;&nbsp;&nbsp;&nbsp;
<?php
}
echo sprintf($data['l10n_midcom']->get('page %d of %d'), $data['page'], $data['last_page']);

if ($data['next_page'] !== null) {
?>
&nbsp;&nbsp;&nbsp;&nbsp;<a href="&(data['next_page_url']);"><?php $data['l10n_midcom']->show('next page'); ?></a></p>
<?php } ?>