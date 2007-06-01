<?php
// Available request keys: controller, indexmode, schema, schemadb

//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<h1><?php echo $data['l10n']->get('edit event'); ?>: <?php echo $data['view_event']['title']; ?></h1>

<?php $data['controller']->display_form(); ?>