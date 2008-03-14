<h1><?php echo sprintf($_MIDCOM->i18n->get_string('edit file %s', 'midgard.admin.asgard'), $data['filename']); ?></h1>

<form method="post" enctype="multipart/form-data" class="datamanager2" action="&(_MIDGARD['uri']:h);" onsubmit="midgard_admin_asgard_file_edit.toggleEditor();">
    <fieldset>
        <legend><?php echo $_MIDCOM->i18n->get_string('upload file', 'midgard.admin.asgard'); ?></legend>

        <input type="file" name="midgard_admin_asgard_file" />
    </fieldset>
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
        <hr />
        <fieldset>
            <legend><?php echo $_MIDCOM->i18n->get_string('edit text file', 'midgard.admin.asgard'); ?></legend>

            <label>
                <span><?php echo $_MIDCOM->i18n->get_string('filename', 'midgard.admin.asgard'); ?></span>
                <input class="text" type="text" name="midgard_admin_asgard_filename" value="<?php echo $data['file']->name; ?>" />
            </label>

            <label>
                <span><?php echo $_MIDCOM->i18n->get_string('file contents', 'midgard.admin.asgard'); ?></span>
                <textarea name="midgard_admin_asgard_contents" cols="60" rows="30" wrap="none" id="midgard_admin_asgard_file_edit" class="&(codepress_type); codepress"><?php
                    $f = $data['file']->open('r');
                    if ($f)
                    {
                        fpassthru($f);
                    }
                    $data['file']->close();
                ?></textarea>
            </label>
        </fieldset>
       <?php
    }
    ?>
    <div class="form_toolbar">
        <input type="submit" class="save" name="midgard_admin_asgard_save" accesskey="s" value="<?php echo $_MIDCOM->i18n->get_string('save', 'midcom'); ?>" />
    </div>
</form>

<form class="urlform" method="post" action="&(data['delete_url']:h);">
    <?php
    $file_url = $_MIDCOM->get_host_prefix() . "midcom-serveattachmentguid-{$data['file']->guid}/{$data['file']->name}";
    $mime_icon = midcom_helper_get_mime_icon($data['file']->mimetype);
    ?>
    <fieldset>
        <legend><?php echo $_MIDCOM->i18n->get_string('file information', 'midgard.admin.asgard'); ?></legend>

        <div class="icon">
            <a href="&(file_url);">
                <img src="&(mime_icon);" />
                <?php echo midcom_helper_filesize_to_string($data['file']->metadata->size) . " {$data['file']->mimetype}"; ?>
            </a>
        </div>

        <label><span><?php echo $_MIDCOM->i18n->get_string('url', 'midgard.admin.asgard'); ?></span>
            <input class="text" type="text" value="&(file_url);" readonly="readonly" />
        </label>
        <br />
        <input type="submit" class="delete" name="f_delete" value="<?php echo $_MIDCOM->i18n->get_string('delete', 'midcom'); ?>" />
    </fieldset>
</form>
