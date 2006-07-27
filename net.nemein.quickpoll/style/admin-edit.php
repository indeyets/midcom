<?php
global $view_topic;
global $view;
global $view_id;
global $view_title;
global $view_options;

$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2>&(view_title);</h2>

<form method="POST">
<div class="form_description"><?php echo $GLOBALS["view_l10n"]->get("poll subject"); ?></div>
<div class="form_field">
  <input type="text" class="shorttext" maxlength="255" name="net_nemein_quickpoll_field[subject]" value="&(view.title);" />
</div>

<div class="form_fieldgroup">
  <div class="form_fieldgroup_title"><?php echo $GLOBALS["view_l10n"]->get("poll options"); ?></div>

  <?php
  foreach ($view_options as $key => $option) {
    ?>
    <div class="form_description"><?php echo sprintf($GLOBALS["view_l10n"]->get("option no %d"),$key); ?></div>
    <div class="form_field">
      <input type="text" class="shorttext" maxlength="255" name="net_nemein_quickpoll_field[option][&(key);]" value="&(option);" />
    </div>
    <?php
  }
  ?>

  <div class="form_description"><?php echo $GLOBALS["view_l10n"]->get("new option"); ?></div>
  <div class="form_field">
    <input type="text" class="shorttext" maxlength="255" name="net_nemein_quickpoll_field[new_option]" />
  </div>
</div>

<div class="form_toolbar">
  <input type="submit" name="net_nemein_quickpoll_submit" value="Save">
</div>
</form>