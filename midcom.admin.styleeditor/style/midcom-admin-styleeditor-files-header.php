<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<div class="file-manager">
    <div class="filelist">
        <?php
        if (count($data['files']) > 0)
        {
            echo "<ul>\n";
            foreach ($data['files'] as $file)
            {
                $mime_icon = midcom_helper_get_mime_icon($file->mimetype);
                
                $class = '';
                if (   isset($data['file'])
                    && $file->name == $data['file']->name)
                {
                    $class = ' class="selected"';
                }
                
                echo "<li{$class}><a href=\"{$prefix}__mfa/styleeditor/files/{$file->name}\"><img src=\"{$mime_icon}\" /> {$file->name}</a></li>\n";
            }
            echo "</ul>\n";
        }
        else
        {
            echo "<p>" . $_MIDCOM->i18n->get_string('no files', 'midcom.admin.styleeditor') . "</p>\n";
        }
        ?>
    </div>
    <div class="main">
