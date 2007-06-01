<?php
// Bind the view data, remember the reference assignment:
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$dn_data= $data['datamanager']->get_array();

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$prefix .= $data['aegir_interface']->current . "/";
if ($data['object_type'] == 'article' ) {
    $title = $data['l10n']->get('delete article') .': '. htmlspecialchars($data['title']);
} elseif ($data['object_type'] == 'topic') {
    $title = $data['l10n']->get('delete topic') .': '. htmlspecialchars($data['name']);
} elseif (array_key_exists('title', $data)) {
    $title = $data['title'];
} else{
    $title = "";
}
?>

<h2>Delete object of type : <?php echo $data['object_type']; ?></h2>
<p><?php echo $data['l10n_midcom']->get('Are you sure you want to delete this object?'); ?>

<form action="&(prefix);delete/&(dn_data['_storage_guid']);" method="post">
  <input type="submit" name="admin_content_aegir_deleteok" value="<?php echo $data['l10n_midcom']->get('delete'); ?> " />
  <input type="submit" name="admin_content_aegir_deletecancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
</form>
</p>
<?php $data['datamanager']->display_view (); ?>
