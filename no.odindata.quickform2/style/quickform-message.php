<?php
// Get the field name
foreach ($data['schemadb'] as $schema_name => $object)
{
    break;
}

$indent = '| ';
$current_indent = '';

// Loop through the all of the fields
foreach ($data['schemadb'][$schema_name]->fields as $key => $field)
{
    // Convert legacy names
    if (   isset($field['start_fieldgroup'])
        && !isset($field['start_fieldset']))
    {
        $field['start_fieldset'] = $field['start_fieldgroup'];
    }
    
    // Show first the fieldsets
    if (isset($field['start_fieldset']))
    {
         // There may be two different sets of fieldsets
        if (isset($field['start_fieldset']['title']))
        {
            echo $current_indent . "{$field['start_fieldset']['title']}\n";
            echo $current_indent . no_odindata_quickform2_viewer::underline($field['start_fieldset']['title']);
            
            // Increase the indentation
            $current_indent = "{$current_indent}{$indent}";
            echo $current_indent . "\n";
            
            if (isset($field['start_fieldset']['description']))
            {
                echo "{$current_indent}{$field['start_fieldset']['description']}\n";
            }
        }
        else
        {
            foreach ($field['start_fieldset'] as $key => $array)
            {
                if (   !is_array($array)
                    || !isset($array['title']))
                {
                    continue;
                }
                
                no_odindata_quickform2_viewer::underline($array['title']);

                if (isset($array['description']))
                {
                    echo "{$current_indent}\n";
                    echo "{$current_indent}{$array['description']}\n";
                }
            }
        }
        
        echo $current_indent . "\n";
    }
    
    $title = $data['schemadb'][$schema_name]->translate_schema_string($field['title']);
    
    // Output the field title
    echo "{$current_indent}{$title}\n";
    echo $current_indent . no_odindata_quickform2_viewer::underline($title, '-');
    echo $current_indent . "\n";
    
    $type_field = $data['controller']->datamanager->types[$key];
    
    // Type specific output
    switch (get_class($type_field))
    {
        case 'midcom_helper_datamanager2_type_select':
            $index = $type_field->convert_to_storage(null);
            $value = $type_field->get_name_for_key($index);
            break;
        
        default:
            $value = $type_field->convert_to_storage(null);
    }
    
    echo "{$current_indent}{$value}\n";
    echo "{$current_indent}\n";
    
    // Convert legacy names
    // Convert legacy names
    if (   isset($field['end_fieldgroup'])
        && !isset($field['end_fieldset']))
    {
        $field['end_fieldset'] = $field['end_fieldgroup'];
    }
    
    // Remove the indentation for each fieldset end
    if (isset($field['end_fieldset']))
    {
        if (is_numeric($field['end_fieldset']))
        {
            for ($i = 0; $i <= $field['end_fieldset']; $i++)
            {
                $current_indent = substr($current_indent, 0, -1 * strlen($indent));
                
                if (!$current_indent)
                {
                    break;
                }
            }
        }
        else
        {
            $current_indent = substr($current_indent, 0, -1 * strlen($indent));
        }
        echo "{$current_indent}\n";
    }
}

?>


-- 
<?php echo sprintf($data['l10n']->get('sent from %s'), $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)); ?> on <?php echo strftime('%c'); ?>
