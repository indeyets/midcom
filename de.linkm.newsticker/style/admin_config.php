<?php
global $view_title;
global $view_config;
global $view_topic;

$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2><?php echo $GLOBALS["view_l10n"]->get("settings"); ?></h2>

<?php $view_config->display_form(); ?>