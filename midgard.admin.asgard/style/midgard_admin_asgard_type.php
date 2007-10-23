<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
if ($data['component'] == 'midgard')
{
    $component_label = 'Midgard CMS';
}
else
{
    $component_label = $_MIDCOM->i18n->get_string($data['component'], $data['component']);
}
echo "<h2>";
echo sprintf($_MIDCOM->i18n->get_string('%s in %s', 'midcom'), 
        midgard_admin_asgard_plugin::get_type_label($data['type']),
        $component_label);
echo "</h2>";

if ($data['component'] == 'midgard')
{
    echo "<p>" . $_MIDCOM->i18n->get_string('this is a midgard core type', 'midgard.admin.asgard') . "</p>\n";
}
else
{
    echo "<p>" . sprintf($_MIDCOM->i18n->get_string('this type belongs to %s component', 'midgard.admin.asgard'), $data['component']) . "</p>\n";
}
?>

&(data['help']:h);

<?php
$qb = new midgard_query_builder($data['type']);
$qb->include_deleted();
$qb->add_constraint('metadata.deleted', '=', true);
$deleted = $qb->count();
echo "<p><a href=\"{$prefix}__mfa/asgard/trash/{$data['type']}/\">" . sprintf($_MIDCOM->i18n->get_string('%s deleted items', 'midgard.admin.asgard'), $deleted) . "</a></p>\n";
?>