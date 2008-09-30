<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<div class="main">
    <h1><?php echo sprintf($data['l10n']->get('import subscribers to "%s"'), $data['campaign']->title); ?></h1>

    <p>
        <?php echo $data['l10n']->get('you can import simple lists of email addresses here'); ?>. 
        <?php echo $data['l10n']->get('you can also write the addresses to the text field'); ?>.
    </p>

    <form enctype="multipart/form-data" action="&(_MIDGARD['uri']);" method="post" class="datamanager datamanager2">
        <label for="org_openpsa_directmarketing_import_upload">
            <span class="field_text"><?php echo $data['l10n']->get('file to import'); ?></span>
            <input type="file" class="fileselector" name="org_openpsa_directmarketing_import_upload" id="org_openpsa_directmarketing_import_upload" />
        </label>
        <label for="org_openpsa_directmarketing_import_textarea">
            <span class="field_text"><?php echo $data['l10n']->get('email addresses'); ?></span>
            <textarea class="longtext" name="org_openpsa_directmarketing_import_textarea" id="org_openpsa_directmarketing_import_textarea" cols="40" rows="20"></textarea>
        </label>
        <label for="org_openpsa_directmarketing_import_separator">
            <span class="field_text"><?php echo $data['l10n']->get('address separator'); ?></span>
            <select class="dropdown" name="org_openpsa_directmarketing_import_separator" id="org_openpsa_directmarketing_import_separator">
                <option value="N">\n (<?php echo $data['l10n']->get('newline'); ?>)</option>
                <option value=",">, (<?php echo $data['l10n']->get('comma'); ?>)</option>
                <option value=";">; (<?php echo $data['l10n']->get('semicolon'); ?>)</option>
            </select>
        </label>
        <div class="form_toolbar">
            <input type="submit" class="save" value="<?php echo $data['l10n']->get('import'); ?>" />
        </div>
    </form>
</div>
