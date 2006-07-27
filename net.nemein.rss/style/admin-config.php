<?php
global $view_config;
global $view_topic;
global $view_layouts;

$pdomain = "net.nemein.rss";
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<form method="post" action="&(prefix);config">

<h2><?=$GLOBALS["view_l10n_midcom"]->get("settings") ?></h2>

<div class="form_description"><?php echo $GLOBALS["view_l10n"]->get("aggregator settings"); ?></div>
<div class="form_field">
  <?php echo $GLOBALS["view_l10n"]->get("subscription mode"); 
  if ($view_config->get("subscription_mode") == "bloglines") {
    $local_selected = "";
    $bloglines_selected = " selected=\"selected\"";
  } else {
    $bloglines_selected = "";
    $local_selected = " selected=\"selected\"";
  }
  ?>
  <select name="fconfig_subscription_mode">
    <option value="local"&(local_selected:h);><?php echo $GLOBALS["view_l10n"]->get("local"); ?></option>
    <option value="bloglines"&(bloglines_selected:h);><?php echo $GLOBALS["view_l10n"]->get("bloglines"); ?></option>
  </select>
</div>

<div class="form_field">
  <?php echo $GLOBALS["view_l10n"]->get("aggregator mode"); 
  if ($view_config->get("topic_mode") == "channels") {
    $combined_selected = "";
    $channels_selected = " selected=\"selected\"";
  } else {
    $channels_selected = "";
    $combined_selected = " selected=\"selected\"";
  }
  ?>
  <select name="fconfig_topic_mode">
    <option value="combined"&(combined_selected:h);><?php echo $GLOBALS["view_l10n"]->get("combined"); ?></option>
    <option value="channels"&(channels_selected:h);><?php echo $GLOBALS["view_l10n"]->get("channels"); ?></option>
  </select>
</div>
<div class="form_field">
  <?php echo $GLOBALS["view_l10n"]->get("display items"); 
  if ($view_config->get("topic_item_display") == "titles") {
    $details_selected = "";
    $titles_selected = " selected=\"selected\"";
  } else {
    $titles_selected = "";
    $details_selected = " selected=\"selected\"";
  }
  ?>
  <select name="fconfig_topic_item_display">
    <option value="titles"&(titles_selected:h);><?php echo $GLOBALS["view_l10n"]->get("titles"); ?></option>
    <option value="details"&(details_selected:h);><?php echo $GLOBALS["view_l10n"]->get("details"); ?></option>
  </select>
</div>
<div class="form_field">
  <?php echo $GLOBALS["view_l10n"]->get("feed title display"); 
  if ($view_config->get("topic_name_display") == "local") {
    $remote_selected = "";
    $local_selected = " selected=\"selected\"";
  } else {
    $local_selected = "";
    $remote_selected = " selected=\"selected\"";
  }
  ?>
  <select name="fconfig_topic_name_display">
    <option value="remote"&(remote_selected:h);><?php echo $GLOBALS["view_l10n"]->get("remote from feed"); ?></option>
    <option value="local"&(local_selected:h);><?php echo $GLOBALS["view_l10n"]->get("local"); ?></option>
  </select>
</div>
<div class="form_field">
  <?=$GLOBALS["view_l10n"]->get("latest items to show") ?> <input type="text" size="2" name="fconfig_show_latest" value="<?=$view_topic->parameter($pdomain, "show_latest") ?>" />
</div>

<div class="form_description"><?=$GLOBALS["view_l10n"]->get("rss output settings") ?></div>

<div class="form_field">
  <?=$GLOBALS["view_l10n"]->get("rss description") ?> <input type="text" size="40" name="fconfig_rss_description" value="<?=$view_topic->parameter($pdomain, "rss_description") ?>" />
</div>
<div class="form_field">
  <?=$GLOBALS["view_l10n"]->get("rss items to show") ?> <input type="text" size="2" name="fconfig_rss_items" value="<?=$view_topic->parameter($pdomain, "rss_items") ?>" />
</div>

<div class="form_toolbar">
  <input type="submit" name="fconfig_submit" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("save"); ?>">
  <input type="submit" name="fconfig_cancel" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("cancel"); ?>">
  <input type="reset" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("reset"); ?>">
</div>

</form>