<?php
// Available request keys: article, datamanager, edit_url, delete_url, create_urls

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$dn_data= $data['datamanager']->get_content_html();
?>

<h2><?php echo $data['l10n']->get('delete release'); ?>: &(dn_data['release']:h);</h2>

<form action="" method="post">
  <input type="submit" name="net_nemein_downloads_deleteok" class="delete" accesskey="d" value="<?php echo $data['l10n_midcom']->get('delete'); ?> " />
  <input type="submit" name="net_nemein_downloads_deletecancel" class="cancel" accesskey="c" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
</form>

<?php midcom_show_style('view-release'); ?>
