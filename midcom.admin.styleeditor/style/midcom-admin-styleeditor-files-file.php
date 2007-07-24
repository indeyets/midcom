<h1><?php echo sprintf($_MIDCOM->i18n->get_string('edit file %s', 'midcom.admin.styleeditor'), $data['filename']); ?></h1>

<form method="post" enctype="multipart/form-data">
    <fieldset>
        <legend><?php echo $_MIDCOM->i18n->get_string('upload file', 'midcom.admin.styleeditor'); ?></legend>
        
        <input type="file" name="midcom_admin_styleeditor_file" />
    </fieldset>
    <?php
    if (array_key_exists($data['file']->mimetype, $data['text_types']))
    {
        // Show file for editing only if it is a text file
        ?>
        <hr />
        <fieldset>
            <legend><?php echo $_MIDCOM->i18n->get_string('edit text file', 'midcom.admin.styleeditor'); ?></legend>
            
            <label>
                <span><?php echo $_MIDCOM->i18n->get_string('filename', 'midcom.admin.styleeditor'); ?></span>
                <input class="text" type="text" name="midcom_admin_styleeditor_filename" value="<?php echo $data['file']->name; ?>" />
            </label>

            <label>
                <span><?php echo $_MIDCOM->i18n->get_string('file contents', 'midcom.admin.styleeditor'); ?></span>
                <textarea name="midcom_admin_styleeditor_contents" cols="60" rows="30" wrap="none"><?php
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
        <input type="submit" class="save" name="midcom_admin_styleeditor_save" accesskey="s" value="<?php echo $_MIDCOM->i18n->get_string('save', 'midcom'); ?>" />
    </div>
</form>

<form class="urlform">
    <?php
    $file_url = $_MIDCOM->get_host_prefix() . "midcom-serveattachmentguid-{$data['file']->guid}/{$data['file']->name}";
    $mime_icon = midcom_helper_get_mime_icon($data['file']->mimetype);
    ?>
    <fieldset>
        <legend><?php echo $_MIDCOM->i18n->get_string('file information', 'midcom.admin.styleeditor'); ?></legend>
        
        <div class="icon">
            <a href="&(file_url);">
                <img src="&(mime_icon);" />
                <?php echo midcom_helper_filesize_to_string($data['file']->metadata->size) . " {$data['file']->mimetype}"; ?>
            </a>
        </div>
        
        <label><span><?php echo $_MIDCOM->i18n->get_string('url', 'midcom.admin.styleeditor'); ?></span>
            <input class="text" type="text" value="&(file_url);" readonly="readonly" />
        </label>
    </fieldset>
</form>
