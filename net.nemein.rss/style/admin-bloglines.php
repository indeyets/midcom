<?php
global $view_config;
global $view_form_prefix;
?>
<h4><?php echo $GLOBALS["view_l10n"]->get("bloglines account"); ?></h4>
<div class="form_description">
  <?php echo $GLOBALS["view_l10n_midcom"]->get("username"); ?>
</div>
<div class="form_field">
  <input class="shorttext" type="text" name="&(view_form_prefix);bloglines_username" value="<?php echo $view_config->get("bloglines_username"); ?>" />  
</div>
<div class="form_description">
  <?php echo $GLOBALS["view_l10n_midcom"]->get("password"); ?>
</div>
<div class="form_field">
  <input class="shorttext" type="password" name="&(view_form_prefix);bloglines_password" value="<?php echo $view_config->get("bloglines_password"); ?>" />  
</div>