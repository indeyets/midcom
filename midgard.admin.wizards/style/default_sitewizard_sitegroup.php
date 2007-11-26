<?php
/**
 * This is the styleelement I use to show the index
 * Use this to get variables etc from the handler:
 */
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1><?php echo $data['l10n']->get('new midgard sitegroup'); ?></h1>

<form method="post" name="default_sitewizard_sitegroup">   
  <fieldset>
  <?php echo $data['l10n']->get('sitegroup'); ?><br/>
  <input type="text" name="default_sitewizard_sitegroup"/><br/>
  <?php echo $data['l10n']->get('admin user'); ?><br/>
  <input type="text" name="default_sitewizard_adminuser"/><br/>
  <?php echo $data['l10n']->get('password'); ?><br/>
  <input type="password" name="default_sitewizard_adminpass"/><br/>

  <input type="submit" name="default_sitewizard_sitegroup_submit" value="<?php echo $data['l10n']->get('next'); ?>">
  </fieldset>
</form>