<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');

// Available request data: mode, type, type_config, entry, entry_url, return_url
?>

<h2><?php echo $data['topic']->extra . ': ' . $data['l10n']->get("submit {$data['mode']}"); ?></h2>

<p><?php echo sprintf($data['l10n']->get('entry created, expires in %d days.'), $data['config']->get('expiration_days')); ?></p>

<p><a href="&(data['entry_url']);"><?php $data['l10n']->show('show entry'); ?></a></p>

<p><a href="&(data['return_url']);"><?php $data['l10n_midcom']->show('back'); ?></a></p>