<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<div class="main">
    <h1><?php echo $data['l10n']->get('import'); ?></h1>

    <p>
        <?php
        echo $data['l10n']->get('you can import csv files here');

        // Show instructions
        echo "<ul>\n";
        echo "    <li>" . $data['l10n']->get('one line per product') . "</li>\n";
        echo "    <li>" . $data['l10n']->get('first row is headers') . "</li>\n";
        echo "    <li>" . $data['l10n']->get('iso-latin-1 encoding') . "</li>\n";
        echo "    <li>" . $data['l10n']->get('fields available for matching are defined in schema') . "</li>\n";
        echo "</ul>\n";
        ?>
    </p>

    <form enctype="multipart/form-data" action="&(_MIDGARD['uri']);" method="post" class="datamanager">
        <label for="org_openpsa_products_import_upload">
            <span class="field_text"><?php echo $data['l10n']->get('file to import'); ?></span>
            <input type="file" class="fileselector" name="org_openpsa_products_import_upload" id="org_openpsa_products_import_upload" />
        </label>
        <label for="org_openpsa_products_import_separator">
            <span class="field_text"><?php echo $data['l10n']->get('field separator'); ?></span>
            <select class="dropdown" name="org_openpsa_products_import_separator" id="org_openpsa_products_import_separator">
                <option value=";">;</option>
                <option value=",">,</option>
            </select>
        </label>
        <label for="org_openpsa_products_import_schema">
            <span class="field_text"><?php echo $data['l10n']->get('schema'); ?></span>
            <select class="dropdown" name="org_openpsa_products_import_schema" id="org_openpsa_products_import_schema">
                <?php
                foreach (array_keys($data['schemadb_product']) as $name)
                {
                    echo "                <option value=\"{$name}\">" . $data['l10n']->get($data['schemadb_product'][$name]->description) . "</option>\n";
                }
                ?>
            </select>
        </label>
        <div class="form_toolbar">
            <input type="submit" class="save" value="<?php echo $data['l10n']->get('import'); ?>" />
        </div>
    </form>
</div>
