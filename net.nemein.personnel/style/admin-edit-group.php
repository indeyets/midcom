<?php
// Available request keys: person, controller
// $data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<h1><?php echo sprintf($data['l10n']->get('edit group %s'), $data['group']->official) ?></h1>
<?php $data['controller']->display_form (); ?>