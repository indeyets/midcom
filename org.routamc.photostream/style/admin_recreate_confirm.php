<?php
// Available request keys: article, datamanager, edit_url, delete_url, create_urls

//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<h2><?php echo $data['l10n']->get('recreate derived images'); ?></h2>

<p>
    <?php echo $data['l10n']->get('this operation will take some time'); ?>
</p>

<form action="" method="post">
  <input type="submit" name="org_routamc_photostream_recreateok" value="<?php echo $data['l10n']->get('start'); ?> " />
  <input type="submit" name="org_routamc_photostream_recreatecancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
</form>