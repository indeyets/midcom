<?php
// Available request keys: article, controller, edit_url, delete_url, create_urls
?>

<h2><?php echo $data['l10n']->get('edit spot'); ?>: <?php echo $data['controller']->datamanager->types['title']->value; ?></h2>

<?php $data['controller']->display_form (); ?>