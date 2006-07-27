<?php
global $title;
global $view_topic;
global $view_current_group;
global $view_organizations;
global $view_preferred_person;
global $view_preferred_group;
global $midcom;
$prefix = $midcom->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

function net_nemein_organizations_list_groups_selector($up = NULL, $spacer = '') {
    $midgard = $GLOBALS["midcom"]->get_midgard();
    if (array_key_exists("view", $GLOBALS))
        $owner = $GLOBALS["view"]->owner;
    else
        $owner = -1;
        
    $qb = midcom_baseclasses_database_group::new_query_builder();
    if (is_null($up))
    {
        $qb->add_constraint('owner', '=', 0);
    }
    else
    {
        $qb->add_constraint('owner', '=', $up);
    }
    $groups = $qb->execute();
    
    if (count($groups) > 0)
    {
        foreach ($groups as $group)
        {
            if ($group->sitegroup != $midgard->sitegroup)
            {
                continue;
            }
            if ($group->guid == $GLOBALS["view_current_group"]) 
            {
                echo '<option value="' . $group->guid . '" selected>' . $spacer . $group->name . "</option>\n";
            } 
            else 
            {
                echo '<option value="' . $group->guid . '">' . $spacer . $group->name . "</option>\n";
            }
            
            net_nemein_organizations_list_groups_selector($group->id, "{$spacer}&nbsp;&nbsp;&nbsp;&nbsp;");
        }
    }
}
?>

<?php 
if (   $_MIDCOM->auth->can_do('midgard:update', $view_topic)
    && $_MIDCOM->auth->can_do('midcom:component_config', $view_topic))
{
?>
<form name="net_nemein_personnel_configform" method="POST" action="&(prefix);">

<p><?php echo $GLOBALS["view_l10n"]->get("select group to display"); ?><br>
<select name="net_nemein_organizations_config[group]">
  <?php net_nemein_organizations_list_groups_selector(); ?>
</select></p>
<?php if (count($view_organizations) > 0) { ?>
<p><?php echo $GLOBALS["view_l10n"]->get("select organization to display first"); ?><br>
<select name="net_nemein_organizations_config[preferred_group]">
<option value="false"><?php echo $GLOBALS["view_l10n"]->get("none"); ?></option>
<?php 
foreach ($view_organizations as $organizations_guid => $organizations_name) {
  $organizations_selected = "";
  if ($organizations_guid == $view_preferred_group) {
    $organizations_selected = " selected=\"selected\"";
  }
  ?>
  <option value="&(organizations_guid);"&(organizations_selected:h);>&(organizations_name);</option>
  <?php
}
?>
</select></p>
<?php } ?>
<p><input type="submit" name="net_nemein_organizations_submit" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("select"); ?> &raquo;">
</p>
</form>
<?php } ?>