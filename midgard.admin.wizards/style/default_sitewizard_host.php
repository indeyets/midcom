<?php
/**
 * This is the styleelement I use to show the index
 * Use this to get variables etc from the handler:
 */
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1><?php echo $data['l10n']->get('new midgard host'); ?></h1>

<form method="post" name="default_sitewizard_host">   
  <fieldset>
  <?php echo $data['l10n']->get('site name'); ?><br/>
  <input type="text" name="default_sitewizard_sitename"/><br/>
  <?php echo $data['l10n']->get('host'); ?><br/>  
  <input type="text" name="default_sitewizard_host"/><br/>
  <?php echo $data['l10n']->get('prefix'); ?><br/>
  <input type="text" name="default_sitewizard_prefix"/><br/>
  <?php echo $data['l10n']->get('port'); ?><br/>
  <input type="port" name="default_sitewizard_port"/><br/>

  <input type="submit" name="default_sitewizard_host_submit" value="<?php echo $data['l10n']->get('next'); ?>">
  </fieldset>
</form>
