<?php
// Available request keys: article, datamanager, edit_url, delete_url, create_urls
$view = $data['datamanager']->get_content_html();
?>

<h2><?php echo $data['l10n']->get('delete photo'); ?>: &(view['title']);</h2>

<form action="" method="post">
  <input type="submit" name="org_routamc_photostream_deleteok" value="<?php echo $data['l10n_midcom']->get('delete'); ?> " />
  <input type="submit" name="org_routamc_photostream_deletecancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
</form>

<?php midcom_show_style('show_photo'); ?>