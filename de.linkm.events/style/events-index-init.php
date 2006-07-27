<?php
global $view_config;
?>

<h1><?php echo $view_config->get("title"); ?></h1>

<table>
  <thead>
    <tr>
      <th><?php echo $GLOBALS["view_l10n"]->get("date"); ?></th>
      <th><?php echo $GLOBALS["view_l10n"]->get("event"); ?></th>
      <th><?php echo $GLOBALS["view_l10n"]->get("location"); ?></th>
    </tr>
  </thead>
