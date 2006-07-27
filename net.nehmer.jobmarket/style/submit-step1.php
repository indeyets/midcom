<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');

// Available request data: mode, type, type_config, controller
?>

<h2><?php echo $data['topic']->extra . ': ' . $data['l10n']->get("submit {$data['mode']}"); ?></h2>

<?php $data['controller']->display_form(); ?>