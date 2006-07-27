<?php
global $view_form_prefix;
global $view_feed;
global $view_topic;
global $view;
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h2><?php echo $GLOBALS["view_l10n_midcom"]->get("edit"); ?>: &(view_feed->title);</h2>

<form method="POST" name="&(view_form_prefix);form">
<div class="form_description">
  <?php echo $GLOBALS["view_l10n"]->get("feed title"); ?>
</div>
<div class="form_field">
  <input class="shorttext" name="&(view_form_prefix);feed[title]" maxlength="255" value="&(view_feed.title);">  
</div>
<div class="form_description">
  <?php echo $GLOBALS["view_l10n_midcom"]->get("url name"); ?>
</div>
<div class="form_field">
  <input class="shorttext" name="&(view_form_prefix);feed[name]" maxlength="255" value="&(view_feed.name);">  
</div>
<div class="form_description">
  <?php echo $GLOBALS["view_l10n"]->get("feed url"); ?>
</div>
<div class="form_field">
  <input class="shorttext" name="&(view_form_prefix);feed[url]" maxlength="255" value="&(view_feed.url);">  
</div>
<div class="form_description">
  <?php echo $GLOBALS["view_l10n"]->get("feed icon url"); ?>
</div>
<div class="form_field">
  <input class="shorttext" name="&(view_form_prefix);feed[icon_url]" maxlength="255" value="&(view_feed.extra1);">  
</div>
<div class="form_toolbar">
  <input type="submit" name="&(view_form_prefix);submit" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("save"); ?>">
  <input type="submit" name="&(view_form_prefix);cancel" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("cancel"); ?>">
</div>
</form>