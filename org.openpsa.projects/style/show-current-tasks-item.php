<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
//echo "<dt><input type=\"checkbox\" /><a href=\"{$node[MIDCOM_NAV_FULLURL]}task/{$view_data['task']->guid}/\">{$view_data['task']->title}</a></dt>\n"; 
?>
    <dt>
        <form method="post" action="<?php echo $node[MIDCOM_NAV_FULLURL]; ?>workflow/<?php echo $view_data['task']->guid; ?>">
<?php
if ($view_data['task']->status >= ORG_OPENPSA_TASKSTATUS_COMPLETED)
{
    $action = 'remove_complete';
    $checked = ' checked="checked"';
}
else
{
    $action = 'complete';
    $checked = '';
}
//Set rejected etc status classes
switch($view_data['task']->status)
{
    case ORG_OPENPSA_TASKSTATUS_REJECTED:
        $status_class = 'org_openpsa_status_rejected';
        break;
    default:
        $status_class = '';
        break;
}
//TODO: Check deliverables
//NOTE: The hidden input is there on purpose, if we remove a check from checkbox, it will not get posted at all...
?>
        <input type="hidden" name="org_openpsa_projects_workflow_action[&(action);]" value="true" />
        <input type="checkbox"&(checked:h); name="org_openpsa_projects_workflow_dummy" value="true" onChange="this.form.submit()" class="completion" /><a class="&(status_class);" href="<?php echo $node[MIDCOM_NAV_FULLURL]; ?>task/<?php echo $view_data['task']->guid; ?>/"><?php echo $view_data['task']->title; ?></a>
        <?php
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
        ?>
        </form>
        <dd>
            <?php
            if (   array_key_exists('hours_widget', $view_data)
                && array_key_exists($view_data['task']->guid, $view_data['hours_widget']))
            {
                ?>
                <ul class="task_tools">
                    <li><input type="button" onClick="ooToggleHourWidgetDisplay('<?php echo $view_data['task']->guid; ?>');" class="hours" value="<?php echo $view_data['l10n']->get('report hours'); ?>" /></li>
                </ul>
                <?php 
                $view_data['hours_widget'][$view_data['task']->guid]->show(false); 
            } 
            ?>
        </dd>
    </dt>

