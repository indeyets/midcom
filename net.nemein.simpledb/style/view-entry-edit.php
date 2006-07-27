<?php
  global $view;
  global $view_title;
  global $view_datamanager;
  global $midcom;
  $prefix = $midcom->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h1>&(view_title);: <?echo htmlspecialchars($view["title"]); ?></h1>

<?php $view_datamanager->display_form(); ?>