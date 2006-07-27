<?php
global $view, $view_form_prefix, $view_query;
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<form method="POST" action="&(prefix);">
  <label for="net_nemein_simpledb_search">
    <?php echo $GLOBALS["view_l10n"]->get("search"); ?>
    <input id="net_nemein_simpledb_search" type="text" name="&(view_form_prefix);query" value="&(view_query);" />
  </label>
  <input type="submit" name="&(view_form_prefix);query_submit" value="<?php echo $GLOBALS["view_l10n"]->get("go"); ?>" />
</form>