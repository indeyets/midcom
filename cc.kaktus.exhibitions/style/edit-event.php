<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1><?php echo sprintf($data['l10n']->get('edit %s'), $data['layout']); ?></h1>
<?php $data['controller']->display_form(); ?>
