<h1><?php echo sprintf($data['l10n']->get('edit host %s'), "{$data['host']->name}{$data['host']->prefix}"); ?></h1>
<?php
$data['controller']->display_form();
?>