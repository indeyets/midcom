<?php
// Available request keys: controller, indexmode, schema, schemadb
?>

<h1><?php echo $data['l10n']->get('create poll'); ?>: <?php echo $data['topic']->extra; ?></h1>

<?php $data['controller']->display_form (); ?>