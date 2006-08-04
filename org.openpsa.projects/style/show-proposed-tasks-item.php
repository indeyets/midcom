<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
echo "<dt><a href=\"{$node[MIDCOM_NAV_FULLURL]}task/{$view_data['task']->guid}/\">{$view_data['task']->title}</a>";
if ($view_data['task']->up)
{
    $parent = $view_data['task']->get_parent();
    if ($parent->orgOpenpsaObtype == ORG_OPENPSA_OBTYPE_PROJECT)
    {
        $parent_url = "{$node[MIDCOM_NAV_FULLURL]}project/{$parent->guid}/";
    }
    else
    {
        $parent_url = "{$node[MIDCOM_NAV_FULLURL]}task/{$parent->guid}/";
    }
    echo " <span class=\"metadata\">(<a href=\"{$parent_url}\">{$parent->title}</a>)</span>";
}
echo "</dt>\n";
echo "<dd>\n";
if ($view_data['task']->manager)
{
    $manager = new midcom_baseclasses_database_person($view_data['task']->manager);
    $contact = new org_openpsa_contactwidget($manager);
    echo sprintf($view_data['l10n']->get("from %s"), $contact->show_inline());
}

if ($_MIDCOM->auth->can_do('midgard:update', $view_data['task'])
    && isset($view_data['task']->resources[$_MIDGARD['user']]))
{
?>
<form method="post" action="<?php echo $node[MIDCOM_NAV_FULLURL]; ?>workflow/<?php echo $view_data['task']->guid; ?>">
    <!-- TODO: If we need all resources to accept task hide tools when we have accepted and replace with "pending acceptance from..." -->
    <ul class="task_tools">
        <li><input type="submit" name="org_openpsa_projects_workflow_action[accept]" class="yes" value="<?php echo $view_data['l10n']->get('accept'); ?>" /></li>
        <li><input type="submit" name="org_openpsa_projects_workflow_action[decline]" class="no" value="<?php echo $view_data['l10n']->get('decline'); ?>" /></li>
    </ul>
</form>
<?php
}
?>
</dd>