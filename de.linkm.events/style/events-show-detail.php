<?php
global $view;
global $view_config;
?>
<h1>&(view["title"]);</h1>

<dl>
  <?php if (isset($view["date"]["timestamp"]) && $view["date"]["timestamp"]) { ?>
    <dt><?php echo $GLOBALS["view_l10n"]->get("date"); ?>:</dt>
      <dd><?php 
      if (isset($view["startdate"]["timestamp"]) && $view["startdate"]["timestamp"] > 0) {
        // If both dates
        echo de_linkm_events_helpers_timelabel($view["startdate"]["timestamp"],$view["date"]["timestamp"]);
      } else {
        // View only the end date
        echo $view["date"]["local_strfulldate"];
      } ?></dd>
  <?php }
  if ($view["location"]) { ?>
    <dt><?php echo $GLOBALS["view_l10n"]->get("location"); ?>:</dt>
      <dd>&(view["location"]);</dd>
  <?php } ?>
</dl>

&(view["description"]:h);