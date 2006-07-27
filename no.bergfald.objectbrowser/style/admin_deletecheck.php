<?php
// Bind the view data, remember the reference assignment:
$request_data =& $_MIDCOM->get_custom_context_data('request_data');

$data = $request_data['datamanager']->get_array();

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$prefix .= $request_data['aegir_interface']->current . "/";
if ($request_data['object_type'] == 'article' ) {
    $title = $request_data['l10n']->get('delete article') .': '. htmlspecialchars($data['title']);
} elseif ($request_data['object_type'] == 'topic') {
    $title = $request_data['l10n']->get('delete topic') .': '. htmlspecialchars($data['name']);
} elseif (array_key_exists('title', $request_data)) {
    $title = $request_data['title'];
} else{
    $title = "";
}
?>

<h2>Delete object of type : <?php echo $request_data['object_type']; ?></h2>
<p><?php echo $request_data['l10n_midcom']->get('Are you sure you want to delete this object?'); ?>

<form action="&(prefix);delete/&(data['_storage_guid']);" method="post">
  <input type="submit" name="admin_content_aegir_deleteok" value="<?php echo $request_data['l10n_midcom']->get('delete'); ?> " />
  <input type="submit" name="admin_content_aegir_deletecancel" value="<?php echo $request_data['l10n_midcom']->get('cancel'); ?>" />
</form>
</p>
<?php $request_data['datamanager']->display_view (); ?>
