<?php
global $view_config;
global $view_topic;
global $view_layouts;

$pdomain = "net.nemein.discussion";
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<form method="post" action="&(prefix);config">

<h2><?=$GLOBALS["view_l10n"]->get("settings") ?></h2>

<div class="form_description"><?php echo $GLOBALS["view_l10n"]->get("moderator group"); ?></div>
<div class="form_field">
  <?php echo $GLOBALS["view_l10n"]->get("moderator group"); ?>
  <select name="fconfig_moderator_group"><?php
    $groups = mgd_list_groups();
    if ($groups) {
      while ($groups->fetch()) {

        $groups_guid = $groups->guid;
        $groups_selected = "";
        if ($groups_guid == $view_config->get("moderator_group")) {
          $groups_selected = " selected=\"selected\"";
        }
        ?>
        <option value="&(groups_guid);"&(groups_selected:h);>&(groups.name);</option>
        <?php

      }
    }
  ?></select>
</div>

<div class="form_field">
  <?php echo $GLOBALS["view_l10n"]->get("thread schema"); ?>
  <select name="fconfig_default_schema"><?php
    foreach ($view_layouts as $layout => $desc) { 
      ?><option value="&(layout);">&(desc);</option><?php 
    }
  ?></select>
</div>
<div class="form_field_checkbox">
  <span class="form_description_checkbox">
    <input type="checkbox" name="fconfig_email_replies" value="1"<?php if ($view_config->get("email_replies")) echo " checked"; ?> /> <?=$GLOBALS["view_l10n"]->get("email replies to author") ?>
  </span>
</div>
<div class="form_field">
  <?=$GLOBALS["view_l10n"]->get("default email") ?> <input type="text" size="40" name="fconfig_default_reply_to" value="<?php echo $view_config->get("default_reply_to"); ?>" />
</div>
<div class="form_field_checkbox">
  <span class="form_description_checkbox">
    <input type="checkbox" name="fconfig_prefer_default_reply_to" value="1"<?php if ($view_config->get("prefer_default_reply_to")) echo " checked"; ?> /> <?=$GLOBALS["view_l10n"]->get("prefer default email") ?>
  </span>
</div>

<div class="form_description"><?=$GLOBALS["view_l10n"]->get("expert settings") ?></div>
<div class="form_field">
  <?=$GLOBALS["view_l10n"]->get("schema database") ?> <input type="text" size="40" name="fconfig_schemadb" value="<?=$view_topic->parameter($pdomain, "schemadb") ?>" />
</div>
<div class="form_field_checkbox">
  <span class="form_description_checkbox">
    <input type="checkbox" name="fconfig_allow_create_by_uri" value="1"<?php if ($view_config->get("allow_create_by_uri")) echo " checked"; ?> /> <?=$GLOBALS["view_l10n"]->get("allow creation by uri") ?>
  </span>
</div>


<div class="form_toolbar">
  <input type="submit" name="fconfig_submit" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("save"); ?>">
  <input type="submit" name="fconfig_cancel" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("cancel"); ?>">
  <input type="reset" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("reset"); ?>">
</div>

</form>