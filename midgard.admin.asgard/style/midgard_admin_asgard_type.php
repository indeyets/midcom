<?php
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