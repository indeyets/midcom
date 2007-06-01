<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
echo "<dt><a href=\"{$node[MIDCOM_NAV_FULLURL]}task/{$data['task']->guid}/\">{$data['task']->title}</a>";
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
echo "</dt>\n";
echo "<dd>\n";
if ($data['task']->manager)
{
    // FIXME: List resources instead
    $qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_projects_task_resource');
    $qb->add_constraint('task', '=', $data['task']->id);
    $ret = $_MIDCOM->dbfactory->exec_query_builder($qb);
    if (   is_array($ret)
        && count($ret) > 0)
    {
        $resources_string = '';
        foreach ($ret as $resource)
        {
            $resource = new midcom_baseclasses_database_person($resource->person);
            $contact = new org_openpsa_contactwidget($resource);
            $resources_string .= ' '.$contact->show_inline();
        }
        echo sprintf($data['l10n']->get("proposed to %s"), $resources_string);
    }
}
?>
</dd>