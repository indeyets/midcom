<?php
global $view;
global $view_config;
global $view_hidden; // yet to be done
global $view_l10n;
global $view_l10n_midcom;

$prefix = $GLOBALS["view_contentmgr"]->viewdata["admintopicprefix"] . "topic/";

$view_style_inherit = $view->parameter("midcom", "style_inherit");
$view_style = $view->parameter("midcom", "style");
$view_navorder = $view->parameter("midcom.helper.nav", "navorder");

$view_navorder_list = array(
    MIDCOM_NAVORDER_DEFAULT => "default sort order",
    MIDCOM_NAVORDER_TOPICSFIRST => "topics first",
    MIDCOM_NAVORDER_ARTICLESFIRST => "articles first",
    MIDCOM_NAVORDER_SCORE => "by score",
);

/* Collect all viewer groups of the topic in question and define the corresponding helper function */
global $viewer_groups;
// $params = $view->listparameters("ViewerGroups");
$viewer_groups = Array();
/*
if ($params)
    while ($params->fetch())
        $viewer_groups[] = $params->name;
if (count($viewer_groups) == 0)
    */
$viewer_groups[] = "all";

function midcom_admin_content_list_viewergroups_selector($up = null, $spacer = '') {
    global $viewer_groups;
    global $view_l10n;
    $midgard = $GLOBALS["midcom"]->get_midgard();

    if (is_null ($up))
        $groups = mgd_list_groups(0);
    else
        $groups = mgd_list_groups($up);

    if ($groups) {
        while ($groups->fetch()) {
            if ($groups->sitegroup != $midgard->sitegroup || substr($groups->name,0,2)==='__')
                continue;

            $group = mgd_get_group($groups->id);

            // Don't show groups deeper in hierarchy as toplevel
            if (is_null($up) && $group->owner != 0)
                continue;

            $guid = $group->guid();

            if (in_array($guid, $viewer_groups))
                echo '<option value="' . $guid . '" selected="selected">' . $spacer . $groups->name . "</option>\n";
            else
                echo '<option value="' . $guid . '">' . $spacer . $groups->name . "</option>\n";
            midcom_admin_content_list_viewergroups_selector($group->id, $spacer."&nbsp;&nbsp;&nbsp;&nbsp;");
        }
    }
}

function midcom_admin_content_list_groups_selector($up = null, $spacer = '') {
    $midgard = $GLOBALS["midcom"]->get_midgard();
    if (array_key_exists("view",$GLOBALS))
        $owner = $GLOBALS["view"]->owner;
    else
        $owner = -1;
    if (is_null ($up))
        $groups = mgd_list_groups();
    else
        $groups = mgd_list_groups($up);
    if ($groups) {
        while ($groups->fetch()) {
            if ($groups->sitegroup != $midgard->sitegroup)
                continue;
            // Don't show groups deeper in hierarchy as toplevel
            if (is_null($up)) {
                $group = mgd_get_group($groups->id);
                if ($group->owner != 0) {
                  continue;
                }
            }
            if ($owner == $groups->id)
                echo '<option value="' . $groups->id . '" selected="selected">' . $spacer . $groups->name . "</option>\n";
            else
                echo '<option value="' . $groups->id . '">' . $spacer . $groups->name . "</option>\n";
            midcom_admin_content_list_groups_selector($groups->id, $spacer."&nbsp;&nbsp;&nbsp;&nbsp;");
        }
    }
}
?>

<div class="aish1"><?php echo $view_l10n->get("edit topic"); ?></div>

<form method="post" action="&(prefix);editok" enctype="multipart/form-data">

<div class="form_description"><?php echo $view_l10n->get("url name"); ?>:</div>
<div class="form_field"><input class="shorttext" name="f_name" type="text" size="50" maxlength="255" value="&(view.name);" /></div>

<div class="form_description"><?php echo $view_l10n->get("title"); ?>:</div>
<div class="form_field"><input class="shorttext" name="f_title" type="text" size="50" maxlength="255" value="&(view.extra);" /></div>

<div class="form_description"><?php echo $view_l10n->get("style"); ?>:</div>
<?php if (is_null($view_config)) { ?>
  <div class="form_field"><input class="shorttext" name="f_style" type="text" size="50" maxlength="255" value="&(view_style);" /></div>
<?php } else { ?>
  <div class="form_field">
    <select class="dropdown" name="f_style">
      <option value=""><?php echo $view_l10n->get("default"); ?></option>
    <?php

function midcom_admin_content_list_styles_selector($up = null, $spacer = '', $path = '/', $show_shared = false) {
  $midgard = $GLOBALS["midcom"]->get_midgard();

  if (array_key_exists("view",$GLOBALS)) {
    $current_style = $GLOBALS["view"]->parameter("midcom", "style");
  } else {
    $current_style = '';
  }

  if (is_null ($up)) {
    $styles = mgd_list_styles();
  } else {
    $styles = mgd_list_styles($up);
  }

  if ($styles) {
    while ($styles->fetch()) {
      $style = mgd_get_style($styles->id);

      if (!$show_shared && ($style->sitegroup != $midgard->sitegroup)) {
        continue;
      }
      if ($show_shared && ($style->sitegroup == $midgard->sitegroup)) {
        continue;
      }

      // Don't show groups deeper in hierarchy as toplevel
      if (is_null($up)) {
        if ($style->up != 0) {
          continue;
        }
      }

      if ($current_style == $path.$styles->name) {
        echo '<option value="' . $path.$styles->name . '" selected="selected">' . $spacer . $styles->name . "</option>\n";
      } else {
        echo '<option value="' . $path.$styles->name . '">' . $spacer . $styles->name . "</option>\n";
      }
      midcom_admin_content_list_styles_selector($styles->id, $spacer."&nbsp;&nbsp;&nbsp;&nbsp;",$path.$styles->name."/",$show_shared);
    }
  }
}


    $component = $view->parameter("midcom", "component");
    $currentstyle = $view->parameter("midcom", "style");

    // If the component-specific styles configuration is set display only those
    if (isset($view_config["components"][$component]["available styles"])) {
      foreach ($view_config["components"][$component]["available styles"] as $style => $name) {
        if ($style == $currentstyle) { ?>
        <option value="&(style);" selected="selected">&(name);</option>
        <?php } else { ?>
        <option value="&(style);">&(name);</option>
        <?php } ?>
    <?php }
    // Otherwise list all styles on the system
    } else {
      midcom_admin_content_list_styles_selector();
      midcom_admin_content_list_styles_selector(null,'',"/",true);
    } ?>
  </select>
</div>
<?php } // is_null ($view_config) else ?>

<div class="form_field">
  <input name="f_style_inherit" type="checkbox"<?php
    if ($view_style_inherit) echo " checked"; ?>> <?echo $view_l10n->get("inherit style");?>
</div>

<div class="form_description"><?php echo $view_l10n->get("owner"); ?>:</div>
<div class="form_field">
  <select class="dropdown" name="f_owner">
<?php if ($view->owner == 0) { ?>
    <option value="0" selected>Inherit from Parent element</option>
<?php } else { ?>
    <option value="0"><?php echo $view_l10n->get("inherit from parent"); ?></option>
<?php }
    midcom_admin_content_list_groups_selector();
?>
  </select>
</div>

<div class="form_description"><?php echo $view_l10n->get("viewer groups"); ?>:</div>
<div class="form_field">
  <select class="list" name="f_viewer_groups[]" size="6" multiple>
<?php
    if (in_array("all", $viewer_groups))
        echo '<option value="all" selected="selected">' . $view_l10n->get("no access restriction") . "</option>\n";
    else
        echo '<option value="all">' . $view_l10n->get("no access restriction") . "</option>\n";
    midcom_admin_content_list_viewergroups_selector();
?>
  </select>
</div>

<div class="form_description"><?php echo $view_l10n->get("score"); ?>:</div>
<div class="form_field"><input class="shorttext" name="f_score" type="text" size="50" maxlength="5" value="&(view.score);"></div>

<div class="form_description"><?php echo $view_l10n->get("nav order"); ?>:</div>
<div class="form_field">
  <select class="dropdown" name="f_navorder"><?php
  foreach ($view_navorder_list as $value => $caption) {
    ?>
    <option value="&(value);"<?php if ($view_navorder == $value) { ?> selected <?php
     } ?>><?php echo $view_l10n->get($caption); ?></option><?php
  } ?>
  </select>
</div>

<div class="form_toolbar">
  <input type="submit" name="f_submit" value="<?php echo $view_l10n_midcom->get("save"); ?>">
  <input type="submit" name="f_cancel" value="<?php echo $view_l10n_midcom->get("cancel"); ?>">
</div>

</form>
