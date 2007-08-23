<?php
$data = $_MIDCOM->get_custom_context_data('request_data');
?>
<h1><?php echo sprintf($_MIDCOM->i18n->get_string('component configuration for folder %s', 'midcom.helper.dm2config'), $data['topic']->extra); ?></h1>
<?php
$data['controller']->display_form();
?>
