<?php
global $view;
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<div class="net_nemein_discussion_thread">
<h1>&(view["title"]);</h1>

<div class="comment_posted"><?php echo sprintf($GLOBALS["view_l10n"]->get("posted on %s by %s"), $view['created']['local_strfulldate'], $view['poster']); ?></div>

<?php echo $view["content"]["formatted"]; ?>

<div class="thread_toolbar">
<ul>
  <li><a href="&(prefix);<?php
  if (isset($_REQUEST["startfrom"]) && $_REQUEST["startfrom"]) {
      echo "?startfrom=".$_REQUEST["startfrom"];
  } 
  ?>"><img src="<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/16x16/back.png" alt="<?php echo $GLOBALS["view_l10n"]->get("back to index"); ?>" />
  <?php echo $GLOBALS["view_l10n"]->get("back to index"); ?></a></li>
</ul>
</div>
