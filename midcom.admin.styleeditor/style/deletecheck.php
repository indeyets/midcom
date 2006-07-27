<?php
// Bind the view data, remember the reference assignment:
$request_data =& $_MIDCOM->get_custom_context_data('request_data');

$prefix     = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$name       = $request_data['name'];
$content    = $request_data['content'];
$object_guid= $request_data['object_guid'];
$type_i       = $request_data['l10n']->get($request_data['object_type']);
$type       = $request_data['object_type'];
$object     = $request_data['object'];
$name = ($name == '') ? $request_data['l10n']->get("Name not set"): $name; 

?>
<h2><? echo $request_data['l10n']->get('delete');?> &(type_i);: &(name); (&(object.id);) </h2>
<form action="&(prefix);styleeditor/&(type);/delete/&(object_guid);" method="post">
  <input type="submit" name="styleeditor_deleteok" value="<?php echo $request_data['l10n_midcom']->get('delete'); ?>" />
  <input type="submit" name="admin_content_styleeditor_deletecancel" value="<?php echo $request_data['l10n_midcom']->get('cancel'); ?>" />
</form>



