<?php
// Bind the view data, remember the reference assignment:
$view =& $_MIDCOM->get_custom_context_data('request_data');
$data = $view['datamanager']->get_array();
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2><?php echo $view['l10n']->get('delete article'); ?>: <?php echo htmlspecialchars($data['title']); ?></h2>

<form action="&(prefix);/delete/&(data['_storage_id']);" method="post">
  <input type="submit" name="de_linkm_taviewer_deleteok" value="<?php echo $view['l10n_midcom']->get('delete'); ?> " />
  <input type="submit" name="de_linkm_taviewer_deletecancel" value="<?php echo $view['l10n_midcom']->get('cancel'); ?>" />
</form>

<?php $view['datamanager']->display_view (); ?>
