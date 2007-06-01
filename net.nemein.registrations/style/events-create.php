<?php
// Available request keys:
// controller

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$controller =& $data['controller'];
?>
<h2><?php echo $data['topic']->extra; ?>: <?php $data['l10n']->show('create an event'); ?></h2>

<?php $controller->display_form(); ?>