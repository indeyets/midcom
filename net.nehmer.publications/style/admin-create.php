<?php
// Available request keys: controller, indexmode, schema, schemadb

$data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<h2><?php echo $data['l10n']->get('create publication'); ?>: <?php echo $data['topic']->extra; ?></h2>

<?php $data['controller']->display_form (); ?>