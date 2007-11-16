<?php
/**
 * This is the styleelement I use to show the index
 * Use this to get variables etc from the handler:
 */
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1><?php echo $data['l10n']->get('new midgard host'); ?></h1>

<form method="post" name="tkk_sitewizard_host">   
  <fieldset>
  <?php echo $data['l10n']->get('site name'); ?><br/>
  <input type="text" name="tkk_sitewizard_sitename"/><br/>
  <?php echo $data['l10n']->get('host'); ?><br/>
  
  <?php echo $data['current_host']->name = ''; ?>
  
  <input type="text" name="tkk_sitewizard_host" value="<?php echo $data['current_host']->name; ?>"/><br/>

  <?php echo $data['l10n']->get('prefix'); ?><br/>
  <input type="text" name="tkk_sitewizard_prefix"/><br/>
  <!--
  port<input type="port" name="tkk_sitewizard_port"/><br/>
  -->
  <input type="submit" name="tkk_sitewizard_host_submit" value="<?php echo $data['l10n']->get('next'); ?>">
  </fieldset>
</form>