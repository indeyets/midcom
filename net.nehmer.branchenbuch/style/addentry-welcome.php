<?php
// The available request keys can be found in the components' API documentation
// of net_nehmer_account_handler_register
//
// Bind the view data, remember the reference assignment:
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<h2><?php echo $data['l10n']->get('add entry') . ": {$data['type']->name}"; ?></h2>

<p><?php $data['l10n']->show('add entry helptext'); ?></p>

<p><a href="&(data['step1_url']);"><?php $data['l10n_midcom']->show('next'); ?></a></p>

<?php if ($data['other_category_urls']) { ?>
<p><?php $data['l10n']->show('add entry to other type helptext'); ?></p>

<ul>
<?php foreach ($data['other_category_urls'] as $url => $description) { ?>
<li><a href="&(url);">&(description);</a></li>
<?php } ?>
</ul>
<?php } ?>