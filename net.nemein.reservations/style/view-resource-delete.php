<?php
// Available request keys: article, datamanager, edit_url, delete_url, create_urls

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['view_resource'];
?>

<h2><?php echo $data['l10n_midcom']->get('delete'); ?>: &(view['title']);</h2>

<form action="" method="post">
  <input type="submit" name="net_nemein_reservations_deleteok" value="<?php echo $data['l10n_midcom']->get('delete'); ?> " />
  <input type="submit" name="net_nemein_reservations_deletecancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
</form>

<?php 
midcom_show_style('view-resource-header'); 
midcom_show_style('view-resource-footer'); 
?>
