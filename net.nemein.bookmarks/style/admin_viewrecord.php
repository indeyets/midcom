<?php
global $view_title;
global $view; 
global $view_descriptions;
global $view_id;

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<dl>
  <?php foreach ($view_descriptions as $name => $description) { ?>
    <dt>&(description);</dt>
    <dd><pre><?php echo htmlspecialchars(@wordwrap($view[$name], 60)); ?></pre></dd>
  <?php } ?>
</dl>
