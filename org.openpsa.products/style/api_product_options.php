<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<div class="main">
    <h1><?php echo $data['l10n']->get('export'); ?></h1>

    <p>
        <?php
        echo $data['l10n']->get('you can export csv files here');
        ?>
    </p>

    <form enctype="multipart/form-data" action="&(prefix);api/product/csv/" method="post" class="datamanager">
        <label for="org_openpsa_products_export_schema">
            <span class="field_text"><?php echo $data['l10n']->get('schema'); ?></span>
            <select class="dropdown" name="org_openpsa_products_export_schema" id="org_openpsa_products_export_schema">
                <?php
                foreach (array_keys($data['schemadb_product']) as $name)
                {
                    echo "                <option value=\"{$name}\">" . $data['l10n']->get($data['schemadb_product'][$name]->description) . "</option>\n";
                }
                ?>
            </select>
        </label>
        <div class="form_toolbar">
            <input type="submit" class="save" value="<?php echo $data['l10n']->get('export'); ?>" />
        </div>
    </form>
</div>
