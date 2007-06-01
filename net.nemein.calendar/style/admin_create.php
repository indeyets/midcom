<?php
// Available request keys: controller, indexmode, schema, schemadb

//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<h1><?php echo $data['l10n']->get('create event'); ?>: <?php echo $data['topic']->extra; ?></h1>

<?php $data['controller']->display_form(); ?>