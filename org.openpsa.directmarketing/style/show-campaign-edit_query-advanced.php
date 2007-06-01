<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
if (isset($_POST['midcom_helper_datamanager_dummy_field_rules']))
{
    $editor_content = $_POST['midcom_helper_datamanager_dummy_field_rules'];
}
else
{
    $editor_content = array2code($data['campaign']->rules);
}

?>
<div class="main">
    <form name="midcom_helper_datamanager__form" enctype="multipart/form-data" method="post" class="datamanager">
        <fieldset class="area">
            <legend><?php echo $data['l10n']->get('edit rules'); ?></legend>
            <label for="midcom_helper_datamanager_dummy_field_rules" id="midcom_helper_datamanager_dummy_field_rules_label">
                <textarea id="midcom_helper_datamanager_dummy_field_rules" name="midcom_helper_datamanager_dummy_field_rules" rows="25" cols="50" class="longtext" >&(editor_content:s);</textarea>
            </label>
        </fieldset>
        <div class="form_toolbar">
            <input name="midcom_helper_datamanager_submit" accesskey="s" class="save" value="<?php echo $data['l10n_midcom']->get('save'); ?>" type="submit">
            <input name="midcom_helper_datamanager_cancel" class="cancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" type="submit">
        </div>
    </form>
</div>
<div class="sidebar">
    <div class="area">
    </div>
</div>