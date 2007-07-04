<?php
// Available request keys: person, controller
// $data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<h1><?php echo $data['l10n']->get('edit group'); ?></h1>
<?php $data['controller']->display_form (); ?>