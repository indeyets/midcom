<form action="" method="post">
  <input type="submit" name="midgard_admin_asgard_deleteok" value="<?php echo $data['l10n_midcom']->get('delete'); ?> " />
  <input type="submit" name="midgard_admin_asgard_deletecancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
</form>
<?php
$data['datamanager']->display_view();
?>