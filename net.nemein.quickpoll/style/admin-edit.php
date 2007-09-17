<?php
// Available request keys: article, controller, edit_url, delete_url, create_urls
?>

<h1><?php echo $data['l10n']->get('edit poll'); ?>: <?php echo $data['controller']->datamanager->types['title']->value; ?></h1>

<?php $data['controller']->display_form (); ?>