<?php
// Available request keys: article, datamanager, edit_url, delete_url, create_urls
$dn_data= $data['datamanager']->get_content_html();
?>

<h2><?php echo $data['l10n']->get('delete spot'); ?>: &(dn_data['title']);</h2>

<form action="" method="post">
  <input type="submit" name="pl_olga_windguru_deleteok" value="<?php echo $data['l10n_midcom']->get('delete'); ?> " />
  <input type="submit" name="pl_olga_windguru_deletecancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
</form>

<?php midcom_show_style('show-spot'); ?>