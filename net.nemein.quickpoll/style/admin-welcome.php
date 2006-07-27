<?php
global $view_layouts;
global $view_topic;
global $view_config;
global $midcom;
$prefix = $midcom->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2><?php echo $GLOBALS["view_l10n_midcom"]->get("settings"); ?></h2>

<?php
if ($view_config) {
  $view_config->display_form(); 
} ?>