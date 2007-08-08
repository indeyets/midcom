<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<div class="main">
    <h1>Import</h1>

    <p>
        <?php echo $data['l10n']->get('match csv columns to database fields'); ?>
    </p>

    <form action="&(prefix);import/&(data['type']);/csv2/" method="post" class="datamanager">
        <input type="hidden" name="org_openpsa_products_import_separator" value="<?php echo $data['separator']; ?>" />
        <input type="hidden" name="org_openpsa_products_import_tmp_file" value="<?php echo $data['tmp_file']; ?>" />
        <table>
            <thead>
                <tr>
                    <th>
                        <?php
                        echo $data['l10n']->get('csv column');
                        ?>
                    </th>
                    <th>
                        <?php
                        echo $data['l10n']->get('example');
                        ?>
                    </th>
                    <th>
                        <?php
                        echo $data['l10n']->get('store to field');
                        ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($data['rows'][0] as $key => $cell)
                {
                    echo "<tr>\n";
                    echo "    <td><label for=\"org_openpsa_products_import_csv_field_{$key}\">{$cell}</label></td>\n";
                    echo "    <td>{$data['rows'][1][$key]}</td>\n";
                    echo "    <td>\n";
                    echo "        <select name=\"org_openpsa_products_import_csv_field[{$key}]\" id=\"org_openpsa_products_import_csv_field_{$key}\">\n";
                    echo "            <option></option>\n";

                    // Show fields from "default" schemas as selectors
                    $schemadb = $data['schemadb'];
                    if (!array_key_exists('default', $schemadb))
                    {
                        // No default schema in this schemadb, skip
                        continue;
                    }

                    foreach ($schemadb['default']->fields as $field_id => $field)
                    {
                        if (   array_key_exists('hidden', $field)
                            && $field['hidden'])
                        {
                            // Hidden field, skip
                            // TODO: We may want to use some customdata field for this instead
                            continue;
                        }

                        $field_label = $schemadb['default']->translate_schema_string($field['title']);
                        echo "            <option value=\"{$field_id}\">{$field_label}</option>\n";
                    }
                    
                    // Show "parent group code" selector
                    echo "            <option value=\"org_openpsa_products_import_parent_group\">Parent group code</option>\n";

                    echo "    </select></td>\n";
                    echo "</tr>\n";
                }
                ?>
            </tbody>
        </table>
        <div class="form_toolbar">
            <input type="submit" class="save" value="<?php echo $data['l10n']->get('import'); ?>" />
        </div>
    </form>
</div>