<?php
// Available request keys: article, datamanager, edit_url, delete_url, create_urls

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$dn_data= $data['datamanager']->get_content_html();
?>

<h1><?php echo $data['l10n']->get('delete task'); ?>: <?php echo $data['task']->title; ?></h1>

<form action="" method="post">
  <input type="submit" name="org_openpsa_projects_deleteok" value="<?php echo $data['l10n_midcom']->get('delete'); ?> " />
  <input type="submit" name="org_openpsa_projects_deletecancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
</form>

<?php
$data['datamanager']->display_view();
?>