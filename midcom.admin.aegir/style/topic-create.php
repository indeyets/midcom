<?php
global $view_config;
global $view_l10n;
global $view_l10n_midcom;

$prefix = $GLOBALS["view_contentmgr"]->viewdata["admintopicprefix"] . "topic/"; 
$mgr =& $_MIDCOM->get_component_loader();

// Get parent component and navorder
$parent_component = FALSE;
$parent_navorder = FALSE;
if (array_key_exists("view", $GLOBALS) && is_object($GLOBALS["view"])) 
{ 
    $parent_component = $GLOBALS["view"]->parameter("midcom", "component");
    $parent_navorder = $GLOBALS["view"]->parameter("midcom", "navorder");
}

$view_navorder_list = array(
    MIDCOM_NAVORDER_DEFAULT => "default sort order",
    MIDCOM_NAVORDER_TOPICSFIRST => "topics first",
    MIDCOM_NAVORDER_ARTICLESFIRST => "articles first",
    MIDCOM_NAVORDER_SCORE => "by score",
);
die("This styleelement should not be in use");
// Get available components
$components = Array();
if (is_null($view_config["components"])) 
{
    // get list of components
    $mgr->load_all();
    $list = $mgr->list_loaded_components();
    foreach ($list as $path) 
    {
        // Skip pure code libraries
        // Skip midcom admin components
        // Skip midgard core components
        if (   $mgr->get_component_property($path, MIDCOM_PROP_PURECODE)
            || substr($path,0,12) == "midcom.admin.content"
            || substr($path,0,7) == "midgard")
        {
            continue;
        }
        $name = $mgr->get_component_property($path, MIDCOM_PROP_NAME);
        $components[$path] = ($name)?"$name ($path)":$name;
    }
} 
else 
{
    foreach ($view_config["components"] as $path => $config)
    {
        $components[$path] = $config["display as"];
    }
}
asort($components);


function midcom_admin_content_list_groups_selector($up = NULL, $spacer = '') 
{
    $midgard = $_MIDCOM->get_midgard();
    if (array_key_exists("view", $GLOBALS))
    {
        $owner = $GLOBALS["view"]->owner;
    }
    else
    {
        $owner = -1;
    }
    
    if (is_null($up))
    {
        $groups = mgd_list_groups(0);
    }
    else
    {
        $groups = mgd_list_groups($up);
    }
    
    if ($groups) 
    {
        while ($groups->fetch()) 
        {
            if ($groups->sitegroup != $midgard->sitegroup || substr($groups->name,0,2)==='__')
            {
                continue;
            }
            // Don't show groups deeper in hierarchy as toplevel
            if (is_null($up)) 
            {
                $group = mgd_get_group($groups->id);
                if ($group->owner != 0) 
                {
                  continue;
                }
            }
            if ($owner == $groups->id)
            {
                echo '<option value="' . $groups->id . '" selected>' . $spacer . $groups->name . "</option>\n";
            }
            else
            {
                echo '<option value="' . $groups->id . '">' . $spacer . $groups->name . "</option>\n";
            }
            midcom_admin_content_list_groups_selector($groups->id,$spacer."&nbsp;&nbsp;&nbsp;&nbsp;");
        }
    }
}
?>


<h1><?php echo $view_l10n->get("create topic"); ?></h1>

<form method="post" action="&(prefix);createok" enctype="multipart/form-data">

<div class="form_description"><?php echo $view_l10n->get("url name"); ?>:</div>
<div class="form_field"><input class="shorttext" name="f_name" type="text" size="50" maxlength="255 value="" /></div>

<div class="form_description"><?php echo $view_l10n->get("title"); ?>:</div>
<div class="form_field"><input class="shorttext" name="f_title" type="text" size="50" maxlength="255" value="" /></div>

<div class="form_description"><?php echo $view_l10n->get("owner"); ?>:</div>
<div class="form_field">
  <select class="dropdown" name="f_owner">
    <option value="0" selected="selected"><?php echo $view_l10n->get("inherit from parent"); ?></option>
    <?php midcom_admin_content_list_groups_selector(); ?>
  </select>
</div>

<div class="form_description"><?php echo $view_l10n->get("type"); ?>:</div>
<div class="form_field">
  <select class="dropdown" name="f_type">
    <?php foreach ($components as $path => $name) { ?>
      <option value="&(path);"<?php if ($path == $parent_component) { echo ' selected="selected"'; } ?>>&(name);</option>
    <?php } ?>
  </select>
</div>

<div class="form_description"><?php echo $view_l10n->get("nav order"); ?>:</div>
<div class="form_field">
  <select class="dropdown" name="f_navorder">
<?php
    foreach ($view_navorder_list as $value => $caption) 
    {
?>
    <option value="&(value);"><?php echo $view_l10n->get($caption); ?></option>
<?php
    } 
?>
  </select>
</div>

<div class="form_toolbar">
  <input type="submit" name="f_submit" value="<?php echo $view_l10n_midcom->get("create"); ?>" />
  <input type="submit" name="f_cancel" value="<?php echo $view_l10n_midcom->get("cancel"); ?>" />
</div>

</form>
