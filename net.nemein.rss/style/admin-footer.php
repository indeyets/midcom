<?php
global $view_form_prefix;
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<div class="form_toolbar">
  <input type="submit" name="&(view_form_prefix);submit" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("save"); ?>">
  <input type="submit" name="&(view_form_prefix);cancel" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("cancel"); ?>">
</div>
</form>