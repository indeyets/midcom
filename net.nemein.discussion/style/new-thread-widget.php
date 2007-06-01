<?php
global $view, $view_id, $view_title, $view_topic;
    
$data = $view->get_array();
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h1>&(view_topic.extra); (<abbr title="net.nemein.discussion"><?php echo $GLOBALS["view_l10n"]->get("discussion forum"); ?></abbr>)</h1>

<h2>&(view_title);: <?echo htmlspecialchars($data["title"]); ?></h2>

<div class="toolbar">
  <a href="&(prefix);edit/&(view_id);"><?php echo $GLOBALS["view_l10n_midcom"]->get("edit"); ?></a>
  <a href="&(prefix);delete/&(view_id);"><?php echo $GLOBALS["view_l10n_midcom"]->get("delete"); ?></a>
</div>

<?
echo "jee";
    $view->display_view ();
?>