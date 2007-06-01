<?php
// Bind the view data, remember the reference assignment:
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$dn_data= $data['datamanager']->get_array();
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h2><?php echo $data['title'] ?></h2>


<form action="&(prefix);simplecontent/<? echo $data['object_type']; ?>/delete/&(data['id']);.html" method="post">
  <input type="submit" name="admin_content_simplecontent_deleteok" value="<?php echo $data['l10n_midcom']->get('delete'); ?> " />
  <input type="submit" name="admin_content_simplecontent_deletecancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
</form>

<?php $data['datamanager']->display_view (); ?>
