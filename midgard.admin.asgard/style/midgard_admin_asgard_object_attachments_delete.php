<h1><?php echo sprintf($_MIDCOM->i18n->get_string('delete file %s', 'midcom.admin.styleeditor'), $data['filename']); ?></h1>
<p><?php echo sprintf($_MIDCOM->i18n->get_string('confirm delete of file %s', 'midcom.admin.styleeditor'), $data['filename']); ?></p>
<?php
if (array_key_exists($data['file']->mimetype, $data['attachment_text_types']))
{
    switch(preg_replace('/.+?\//', '', $data['file']->mimetype))
    {
        case 'css':
            $codepress_type = 'css';
            break;

        case 'html':
            $codepress_type = 'html';
            break;

        case 'x-javascript':
        case 'javascript':
            $codepress_type = 'javascript';
            break;

        default:
            $codepress_type = 'text';
    }

    // Show file for editing only if it is a text file
    ?>
            <textarea name="midcom_admin_styleeditor_contents" cols="60" rows="30" wrap="none" id="midcom_admin_styleeditor_file_edit" class="&(codepress_type);" readonly="readonly"><?php
                $f = $data['file']->open('r');
                if ($f)
                {
                    fpassthru($f);
                }
                $data['file']->close();
            ?></textarea>
   <?php
}
?>
<form method="post" action="&(_MIDGARD['uri']:h);" class="datamanager2">
    <div class="form_toolbar">
        <input type="submit" class="delete" name="f_confirm" value="<?php echo $_MIDCOM->i18n->get_string('delete', 'midcom'); ?>" />
        <input type="submit" class="cancel" name="f_cancel" value="<?php echo $_MIDCOM->i18n->get_string('cancel', 'midcom'); ?>" />
    </div>
</form>