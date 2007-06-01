<h1><?php echo $_MIDCOM->i18n->get_string('style attachments', 'midcom.admin.styleeditor'); ?></h1>

<form method="post" enctype="multipart/form-data">
    <fieldset>
        <legend><?php echo $_MIDCOM->i18n->get_string('upload file', 'midcom.admin.styleeditor'); ?></legend>
        
        <input type="file" name="midcom_admin_styleeditor_file" />
    </fieldset>
    
    <hr />
    
    <fieldset>
        <legend><?php echo $_MIDCOM->i18n->get_string('add text file', 'midcom.admin.styleeditor'); ?></legend>
        
        <label>
            <span><?php echo $_MIDCOM->i18n->get_string('filename', 'midcom.admin.styleeditor'); ?></span>
            <input class="text" type="text" name="midcom_admin_styleeditor_filename" />
        </label>

        <label>
            <span><?php echo $_MIDCOM->i18n->get_string('mimetype', 'midcom.admin.styleeditor'); ?></span>
            <select name="midcom_admin_styleeditor_mimetype">
                <?php
                foreach ($data['text_types'] as $type => $label)
                {
                    $label = $_MIDCOM->i18n->get_string($label, 'midcom.admin.styleeditor');
                    echo "                <option value=\"{$type}\">{$label}</option>\n";
                }
                ?>
            </select>
        </label>
    </fieldset>
    <div class="form_toolbar">
        <input type="submit" class="save" accesskey="s" name="midcom_admin_styleeditor_save" value="<?php echo $_MIDCOM->i18n->get_string('save', 'midcom'); ?>" />
    </div>
</form>
