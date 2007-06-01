<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$dm2 =& $data['datamanager'];
foreach ($dm2->schema->field_order as $field_name)
{
    $field_title =& $data['l10n']->get($dm2->schema->fields[$field_name]['title']);
    $value =& $dm2->types[$field_name]->value;
    echo wordwrap("{$field_title}: {$value}", 75, "\n  ") . "\n";
}
?>