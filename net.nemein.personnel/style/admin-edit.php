<?php
// Available request keys: person, controller
// $data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<h1><?php echo $data['topic']->extra; ?></h1>
<h2><?php echo $data['l10n']->get('edit person'); ?>: <?php echo $data['person']->name; ?></h2>

<?php $data['controller']->display_form (); ?>