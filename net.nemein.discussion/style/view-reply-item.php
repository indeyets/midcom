<?php
global $view;
global $view_level;
global $view_guid;
global $view_form_prefix;
global $view_moderator;
?>
<div class="comment">
  <a name="&(view_guid);"></a><h2 class="comment_title">&(view["title"]);</h2>
  <div class="comment_posted"><?php echo sprintf($GLOBALS["view_l10n"]->get("posted on %s by %s"),$view['created']['local_strfulldate'],$view["poster"]); ?></div>

  <?php echo $view["content"]["formatted"]; ?>
  <div class="comment_toolbar">
  <?php if ($view_moderator) { ?>
      <ul class="midcom_toolbar">
        <li><a href="?&(view_form_prefix);delete_comment=&(view_guid);"><img src="<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/16x16/trash.png" alt="<?php echo $GLOBALS["view_l10n"]->get("delete comment"); ?>" />
        <?php echo $GLOBALS["view_l10n"]->get("delete comment"); ?></a></li>
      </ul>
  <?php } ?>
  </div>
</div>