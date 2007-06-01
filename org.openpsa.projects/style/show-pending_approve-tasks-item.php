<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
//echo "<dt><input type=\"checkbox\" checked=\"checked\" /><a href=\"{$node[MIDCOM_NAV_FULLURL]}task/{$data['task']->guid}/\">{$data['task']->title}</a></dt>\n";
?>
<form method="post" action="<?php echo $node[MIDCOM_NAV_FULLURL]; ?>workflow/<?php echo $data['task']->guid; ?>">
    <dt>
<?php
if ($data['task']->status >= ORG_OPENPSA_TASKSTATUS_COMPLETED)
{
    $action = 'remove_complete';
    $checked = ' checked="checked"';
}
else
{
    $action = 'complete';
    $checked = '';
}
//TODO: Check deliverables
//NOTE: The hidden input is there on purpose, if we remove a check from checkbox, it will not get posted at all...
?>
        <input type="hidden" name="org_openpsa_projects_workflow_action[&(action);]" value="true" />
        <input type="checkbox"&(checked:h); name="org_openpsa_projects_workflow_dummy" value="true" onChange="this.form.submit()" /><a href="<?php echo $node[MIDCOM_NAV_FULLURL]; ?>task/<?php echo $data['task']->guid; ?>/"><?php echo $data['task']->title; ?></a>
        <?php
        if ($data['task']->up)
        {
            $parent = $data['task']->get_parent();
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
        ?>
    </dt>
</form>


<dd>
<?php
//PONDER: Check ACL in stead ?
if ($_MIDGARD['user'] == $data['task']->manager)
{
?>
    <form method="post" action="<?php echo $node[MIDCOM_NAV_FULLURL]; ?>workflow/<?php echo $data['task']->guid; ?>">
        <ul class="task_tools">
            <li><input type="submit" name="org_openpsa_projects_workflow_action[approve]" class="yes" value="<?php echo $data['l10n']->get('approve'); ?>" /></li>
            <!-- PONDER: This is kind of redundant  when one can just remove the checkbox -->
            <li><input type="submit" name="org_openpsa_projects_workflow_action[reject]" class="no" value="<?php echo $data['l10n']->get('dont approve'); ?>" /></li>
        </ul>
    </form>
<?php
}
else if ($data['task']->manager)
{
    $manager = new midcom_baseclasses_database_person($data['task']->manager);
    //TODO: localization
    echo sprintf($data['l10n']->get("pending approval by %s"), $manager->name);
}
?>
</dd>