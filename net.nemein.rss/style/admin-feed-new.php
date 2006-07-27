<?php
global $view_form_prefix;
?>
<h4><?php echo $GLOBALS["view_l10n"]->get("add new feed"); ?></h4>
<div class="form_description">
  <?php echo $GLOBALS["view_l10n"]->get("feed url"); ?>
</div>
<div class="form_field">
  <input class="shorttext" type="text" name="&(view_form_prefix);newfeed[url]" maxlength="255" />  
</div>