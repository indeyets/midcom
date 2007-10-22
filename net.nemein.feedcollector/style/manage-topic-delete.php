<?php
// Available request keys: article, datamanager, edit_url, delete_url, create_urls
$dn_data= $data['datamanager']->get_content_html();
?>
<div id="net_nemein_feedcollector">
<h2><?php echo $data['l10n']->get('delete topic'); ?>: &(dn_data['title']);</h2>

<form action="" method="post">
  <input type="submit" name="net_nemein_feedcollector_deleteok" value="<?php echo $data['l10n_midcom']->get('delete'); ?> " />
  <input type="submit" name="net_nemein_feedcollector_deletecancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
</form>


</div>
