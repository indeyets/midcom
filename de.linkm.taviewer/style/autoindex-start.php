<?php
// Bind the view data, remember the reference assignment:
$view =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h1><?php echo sprintf($view['l10n']->get("directory index for %s"), $view['title']); ?></h1>

<table cellpadding="3">
  <thead>
    <tr>
      <td><?echo $view['l10n']->get("filename");?></td>
      <td><?echo $view['l10n']->get("filedescription");?></td>
      <td><?echo $view['l10n']->get("filetype");?></td>
      <td><?echo $view['l10n']->get("filesize");?></td>
      <td><?echo $view['l10n']->get("file lastmodified");?></td>
    </tr>
  </thead>
