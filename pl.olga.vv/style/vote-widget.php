<?php
global $view;
global $midgard;
global $view_form_prefix;
global $_REQUEST;

$poster = mgd_get_person($midgard->user);
?>
<b><?php echo $GLOBALS["view_l10n"]->get("vote!"); ?></b>
<form method="POST" name="&(view_form_prefix);vote_form" action="&(midgard.uri);">
<div class="form_field">
  <select class="shorttext" name="&(view_form_prefix);vote_value">
   <option></option>
   <option value="1">1</option>
   <option value="2">2</option>
   <option value="3">3</option>
   <option value="4">4</option>
   <option value="5">5</option>
  </select>
<div class="form_toolbar">
  <input type="submit" name="&(view_form_prefix);vote_submit" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("save"); ?>">
</div>
</form>
</div>