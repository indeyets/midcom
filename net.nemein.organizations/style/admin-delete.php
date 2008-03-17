<h1><?php echo "{$data['topic']->extra}: " . $data['l10n']->get('delete group') . ": {$data['group']->official}"; ?></h1>

<p><?php echo $data['l10n']->show('delete group confirmation message'); ?></p>

<?php $data['controller']->display_form(); ?>