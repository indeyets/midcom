<?php
// Available request keys: group, controller

//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<h1><?php echo $data['topic']->extra; ?></h1>
<h2><?php echo $data['l10n']->get('delete group'); ?>: <?php echo $data['group']->name; ?></h2>

<p><?php echo $data['l10n']->show('delete group confirmation message'); ?></p>

<?php $data['controller']->display_form(); ?>