<?php
// Available request keys: article, datamanager, edit_url, delete_url, create_urls

$view =& $_MIDCOM->get_custom_context_data('request_data');
$data = $view['datamanager']->get_content_html();
?>

<h2><?php echo $view['l10n']->get('delete article'); ?>: &(data['title']);</h2>

<form action="" method="post">
  <input type="submit" name="net_nehmer_static_deleteok" value="<?php echo $view['l10n_midcom']->get('delete'); ?> " />
  <input type="submit" name="net_nehmer_static_deletecancel" value="<?php echo $view['l10n_midcom']->get('cancel'); ?>" />
</form>

<?php midcom_show_style('show-article'); ?>
