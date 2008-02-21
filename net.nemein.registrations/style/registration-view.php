<?php
// Available request keys:
// event, registration, registrar, datamanager, is_apprioved, edit_url, delete_url,
// manage_form_url, approve_action, reject_action, rejectdelete_action, rejectnotice_fieldname

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$event =& $data['event'];
$title = sprintf($data['l10n']->get('registration for %s'), $event->title);
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

?>

<h2><?php echo $data['topic']->extra; ?>: &(title);</h2>

<?php
$registrar_controller =& $data['registrar']->create_simple_controller();
$registrar_controller->formmanager->display_view();
?>

<?php
$controller =& $data['registration']->create_simple_controller();
$controller->formmanager->display_view();
?>


<?php midcom_show_style('registration-toolbar'); ?>
<?php midcom_show_style('registration-manage-form'); ?>