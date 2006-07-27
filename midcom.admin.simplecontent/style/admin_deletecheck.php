<?php
// Bind the view data, remember the reference assignment:
$request_data =& $_MIDCOM->get_custom_context_data('request_data');
$data = $request_data['datamanager']->get_array();
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h2><?php echo $request_data['title'] ?></h2>


<form action="&(prefix);simplecontent/<? echo $request_data['object_type']; ?>/delete/&(request_data['id']);.html" method="post">
  <input type="submit" name="admin_content_simplecontent_deleteok" value="<?php echo $request_data['l10n_midcom']->get('delete'); ?> " />
  <input type="submit" name="admin_content_simplecontent_deletecancel" value="<?php echo $request_data['l10n_midcom']->get('cancel'); ?>" />
</form>

<?php $request_data['datamanager']->display_view (); ?>
