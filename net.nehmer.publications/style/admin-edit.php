<?php
// Available request keys: publication, controller, edit_url, delete_url, create_urls

$data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<h2><?php echo $data['l10n']->get('edit publication'); ?>: <?php echo $data['controller']->datamanager->types['title']->value; ?></h2>

<?php $data['controller']->display_form (); ?>