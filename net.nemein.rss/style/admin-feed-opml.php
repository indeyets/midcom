<?php
global $view_form_prefix;
?>
<h4><?php echo $GLOBALS["view_l10n"]->get("import opml subscriptions"); ?></h4>
<div class="form_description">
  <?php echo $GLOBALS["view_l10n"]->get("opml file"); ?>
</div>
<div class="form_field">
  <input type="file" class="fileselector" name="&(view_form_prefix);opml" />
</div>