<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$current_rules =& $data['campaign']->rules;
if (isset($data['new_rule_from'])
    && is_array($data['new_rule_from']))
{
    $generated_from =& $data['new_rule_from'];
}
else if (array_key_exists('generated_from', $current_rules))
{
    $generated_from =& $current_rules['generated_from'];
}
else
{
    $generated_from = array
    (
        'type' => 'AND',
        'rows' => array(),
    );
}

if (!function_exists('list_object_properties'))
{
    // PONDER: Should we support schema somehow (only for non-parameter keys), this would practically require manual parsing...
    function list_object_properties(&$object, &$l10n)
    {
        // These are internal to midgard and/or not valid QB constraints
        $skip_properties = array('realm', 'action', 'errno', 'errstr', 'sitegroup');
        // These will be deprecated soon
        $skip_properties[] = 'orgOpenpsaAccesstype';
        $skip_properties[] = 'orgOpenpsaWgtype';
        if (version_compare(mgd_version(), '1.8.0alpha1', '<='))
        {
            // 1.7 does not allow GUID constraints
            $skip_properties[] = 'guid';
        }
        else
        {
            // 1.8 uses the medatadata object explicitly
            $skip_properties[] = 'created';
            $skip_properties[] = 'creator';
            $skip_properties[] = 'revised';
            $skip_properties[] = 'revisor';
            $skip_properties[] = 'revision';
        }
        if (is_a($object, 'org_openpsa_person'))
        {
            // The info field is a special case
            $skip_properties[] = 'info';
            // These legacy fields are rarely used
            $skip_properties[] = 'topic';
            $skip_properties[] = 'subtopic';
            $skip_properties[] = 'office';
            // This makes very little sense as a constraint
            $skip_properties[] = 'img';
            // Duh
            $skip_properties[] = 'password';
        }
        if (is_a($object, 'midgard_member'))
        {
            // The info field is a special case
            $skip_properties[] = 'info';
        }
        // Skip metadata for now
        $skip_properties[] = 'metadata';
        $ret = array();
        while (list ($property, $value) = each($object))
        {
            if (   preg_match('/^_/', $property)
                || in_array($property, $skip_properties))
            {
                // Skip private or otherwise invalid properties
                continue;
            }
            if (is_object($value))
            {
                while (list ($property2, $value2) = each($value))
                {
                    $prop_merged = "{$property}.{$property2}";
                    $ret[$prop_merged] = $l10n->get("property:{$prop_merged}");
                }
            }
            else
            {
                $ret[$property] = $l10n->get("property:{$property}");
            }
        }
        asort($ret);
        return $ret;
    }
}

$tmp_person = new org_openpsa_person();
$tmp_group = new org_openpsa_organization();
$tmp_member = new midgard_member();
$properties_map = array
(
    'person' => list_object_properties($tmp_person, $data['l10n']),
    'group' => list_object_properties($tmp_group, $data['l10n']),
    'membership' => list_object_properties($tmp_member, $data['l10n']),
);
/*
echo "DEBUG: properties_map<pre>\n";
print_r($properties_map);
echo "</pre>\n";
*/

?>
<!-- Automatically built on PHP level -->
<script language="javascript">
    var org_openpsa_directmarketing_edit_query_property_map = {
<?php
$cnt = count($properties_map);
$i = 0;
foreach ($properties_map as $class => $properties)
{
    $i++;
    echo "        '{$class}': {\n";
    echo "             localized: '" . $data['l10n']->get("class:{$class}") . "',\n";
    echo "             parameters: false,\n";
    echo "             properties: {\n";
    $cnt2 = count($properties);
    $i2 = 0;
    foreach ($properties as $property => $localized)
    {
        $i2++;
    if ($i2 < $cnt2)
    {
            echo "                 {$property}: '{$localized}',\n";
    }
    else
    {
            echo "                 {$property}: '{$localized}'\n";

    }
    }
    echo "             }\n";
    echo "        },\n";
    echo "        '{$class}_parameters': {\n";
    echo "            localized: '" . $data['l10n']->get("class:{$class} parameters") . "',\n";
    echo "            parameters: true,\n";
    echo "            properties: false\n";
    if ($i < $cnt)
    {
        echo "        },\n";
    }
    else
    {
        echo "        }\n";
    }
}
?>
    };
    var org_openpsa_directmarketing_edit_query_match_map = {
        'LIKE': '<?php echo $data['l10n']->get('contains'); ?>',
        'NOT LIKE': '<?php echo $data['l10n']->get('does not contain'); ?>',
        '=': '<?php echo $data['l10n']->get('equals'); ?>',
        '<>': '<?php echo $data['l10n']->get('not equals'); ?>',
        '<': '<?php echo $data['l10n']->get('less than'); ?>',
        '>': '<?php echo $data['l10n']->get('greater than'); ?>'
    };
    var org_openpsa_directmarketing_edit_query_l10n_map = {
        'in_domain': '<?php echo $data['l10n']->get('in domain'); ?>',
        'with_name': '<?php echo $data['l10n']->get('with name'); ?>',
        'add_rule': '<?php echo $data['l10n']->get('add rule'); ?>',
        'remove_rule': '<?php echo $data['l10n']->get('remove rule'); ?>',
        'static_url': '<?php echo MIDCOM_STATIC_URL; ?>'
    }
</script>

<h2>Rules wizard</h2>

<div class="wide">
    <form name="midcom_helper_datamanager__form" id="midcom_helper_datamanager__form" enctype="multipart/form-data" method="post" class="org_openpsa_directmarketing_edit_query">
        <fieldset class="anyalll">
            <?php echo $data['l10n']->get('match:match'); ?>
            <label for="match_any">
                <input type="radio" class="radiobutton" name="midcom_helper_datamanager_dummy_field_type" id="match_any" value="OR"<?php if ($generated_from['type'] == 'OR') echo ' checked'; ?> /> <?php echo $data['l10n']->get('match:any'); ?>
            </label>
            <label for="match_all">
                <input type="radio" class="radiobutton" name="midcom_helper_datamanager_dummy_field_type" id="match_all" value="AND"<?php if ($generated_from['type'] == 'AND') echo ' checked'; ?> /> <?php echo $data['l10n']->get('match:all'); ?>
            </label>
            <?php echo $data['l10n']->get('match:of the following'); ?>:
        </fieldset>
        <input type="hidden" name="midcom_helper_datamanager_dummy_field_rowcount" id="midcom_helper_datamanager_dummy_field_rowcount" value="-1" />
        <div class="form_toolbar" id="midcom_helper_datamanager_form_toolbar">
            <input name="midcom_helper_datamanager_submit" accesskey="s" class="save" value="<?php echo $data['l10n_midcom']->get('save'); ?>" type="submit">
            <input name="midcom_helper_datamanager_cancel" class="cancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" type="submit">
        </div>
        <script language="javascript">
<?php
if (($rowcount = count($generated_from['rows'])) > 0)
{
    $i = 0;
    foreach ($generated_from['rows'] as $row)
    {
        // TODO: Use JS to render ediitor for existing fields.
        echo "            org_openpsa_directmarketing_edit_query_newrow();\n";
        echo "            org_openpsa_directmarketing_edit_query_handler_store[{$i}].set_object('{$row['object']}');\n";
        if (isset($row['property']))
        {
            // Property
            echo "            org_openpsa_directmarketing_edit_query_handler_store[{$i}].set_property('{$row['property']}');\n";
        }
        else
        {
            // Parameter
            echo "            org_openpsa_directmarketing_edit_query_handler_store[{$i}].set_domain('{$row['parameter_domain']}');\n";
            echo "            org_openpsa_directmarketing_edit_query_handler_store[{$i}].set_name('{$row['parameter_name']}');\n";
        }
        echo "            org_openpsa_directmarketing_edit_query_handler_store[{$i}].set_match('{$row['match']}');\n";
        echo "            org_openpsa_directmarketing_edit_query_handler_store[{$i}].set_value('{$row['value']}');\n";
        $i++;
    }
}
else
{
?>
            org_openpsa_directmarketing_edit_query_newrow();
<?php
}
?>
        </script>
    </form>
</div>