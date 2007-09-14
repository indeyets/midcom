<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h2><?php echo sprintf($data['l10n']->get('import codes for "%s"'), $data['device']->title); ?></h2>
<p>
    <?php
    echo $data['l10n']->get('you can import csv files here');

    // Show instructions
    echo "<ul>\n";
    echo "    <li>" . $data['l10n']->get('one line per code') . "</li>\n";
    echo "    <li>" . $data['l10n']->get('UTF-8 encoded') . "</li>\n";
    echo "    <li>" . $data['l10n']->get('first row is column names') . "</li>\n";
    echo "    <li>" . $data['l10n']->get('recognized column names') . "\n";
    echo "        <ul>\n";
    echo "            <li><tt>code</tt> " . $data['l10n']->get('the unique developer code') . " </li>\n";
    echo "            <li><tt>area</tt> " . $data['l10n']->get('the area the code is meant for (can be empty)') . "</li>\n";
    echo "            <li><tt>recipient</tt> " . $data['l10n']->get('GUID of the person this code is meant for (can be empty)') . "</li>\n";
    echo "        </ul>\n";
    echo "    </li>\n";
    //echo "    <li>" . $data['l10n']->get('iso-latin-1 encoding') . "</li>\n";
    //echo "    <li>" . $data['l10n']->get('fields available for matching are defined in schema') . "</li>\n";
    echo "</ul>\n";
    ?>
</p>
<form method="post" enctype="multipart/form-data" class="datamanager2" action="&(prefix);code/import/process.html">
    <input type="hidden" name="org_maemo_devcodes_import_device" value="<?php echo $data['device']->guid; ?>"/>
    <label for="org_maemo_devcodes_import_file"><span class="field_text"><?php echo $data['l10n']->get('file to import'); ?></span>
        <input class="fileselector" id="org_maemo_devcodes_import_file" name="org_maemo_devcodes_import_file" type="file" />
    </label>
    <label for="org_maemo_devcodes_import_separator">
        <span class="field_text"><?php echo $data['l10n']->get('field separator'); ?></span>
        <select class="dropdown" name="org_maemo_devcodes_import_separator" id="org_maemo_devcodes_import_separator">
            <option value=";">;</option>
            <option value=",">,</option>
        </select>
    </label>
    <div class="form_toolbar">
        <input type="submit" class="save" value="<?php echo $data['l10n']->get('import'); ?>" />
    </div>
</form>
