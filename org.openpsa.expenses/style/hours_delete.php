<h1><?php echo $data['l10n']->get('delete hour report'); ?></h1>

<form action="" method="post">
  <input type="submit" name="org_openpsa_contacts_deleteok" value="<?php echo $data['l10n_midcom']->get('delete'); ?> " />
  <input type="submit" name="org_openpsa_contacts_deletecancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
</form>