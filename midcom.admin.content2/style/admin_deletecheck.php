<?php
// Bind the view data, remember the reference assignment:
$request_data =& $_MIDCOM->get_custom_context_data('request_data');
//$data = $request_data['datamanager']->get_array();

$view_l10n = $request_data['l10n_handler'];
?>
<h2><?php echo $view_l10n->get('delete topic'); ?> </h2>

<?php $request_data['datamanager']->formmanager->display_view (); ?>

<p style="font-weight:bold; color: red;"><?php echo $view_l10n->get("descendants are deleted"); ?></p>
<p style="font-weight:bold; color: red;"><?php echo $view_l10n->get("are you sure to delete"); ?>?</p>

<form action="&(request_data['plugin_anchorprefix']);delete/&(request_data['id']);.html" method="post">
  <input type="submit" name="admin_content_simplecontent_deleteok" value="<?php echo $request_data['l10n_midcom']->get('delete'); ?> " />
  <input type="submit" name="admin_content_simplecontent_deletecancel" value="<?php echo $request_data['l10n_midcom']->get('cancel'); ?>" />
</form>
