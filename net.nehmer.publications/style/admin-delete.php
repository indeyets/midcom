<?php
// Available request keys: publication, datamanager, edit_url, delete_url, create_urls

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$dn_data= $data['datamanager']->get_content_html();
?>

<h2><?php echo $data['l10n']->get('delete publication'); ?>: &(dn_data['title']);</h2>

<form action="" method="post">
  <input type="submit" name="net_nehmer_publications_deleteok" value="<?php echo $data['l10n_midcom']->get('delete'); ?> " />
  <input type="submit" name="net_nehmer_publications_deletecancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
</form>

<?php midcom_show_style('show-publication'); ?>