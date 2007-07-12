<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1><?php echo $data['l10n']->get('upload a release'); ?></h1>
<form method="post" action="&(prefix);upload/" class="datamanager2" enctype="multipart/form-data">
    <div class="form">
        <label for="release">
            <?php echo $data['l10n']->get('file'); ?>
        </label>
        <input type="file" name="release" id="release" />
        <br /><br />
    </div>
    <div class="form_toolbar">
        <input type="submit" name="f_submit" class="save" value="<?php echo $data['l10n_midcom']->get('save'); ?>" />
        <input type="submit" name="f_cancel" class="cancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
    </div>
</form>