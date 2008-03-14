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

                $delete_title = sprintf($_MIDCOM->i18n->get_string('delete %s %s', 'midgard.admin.asgard'), $_MIDCOM->i18n->get_string('attachment', 'midgard.admin.asgard'), $file->name);

                echo "<li{$class}><a href=\"{$prefix}__mfa/asgard/object/attachments/{$data['object']->guid}/{$file->name}/\"><img src=\"{$mime_icon}\" width=\"20\" height=\"24\"/> {$file->name}</a> ";
                echo "<a class=\"thickbox\" title=\"{$file->name}\" target=\"_self\" href=\"";
                echo $_MIDCOM->get_host_prefix() . "midcom-serveattachmentguid-{$file->guid}/{$file->name}\">\n";
                echo "<img alt=\"{$file->name}\" src=\"" . MIDCOM_STATIC_URL . "/stock-icons/16x16/view.png\"/>\n";
                echo "</a> \n";
                echo "<a title=\"{$delete_title}\" href=\"";
                echo "{$prefix}__mfa/asgard/object/attachments/delete/{$data['object']->guid}/{$file->name}/\">\n";
                echo "<img alt=\"{$delete_title}\" src=\"" . MIDCOM_STATIC_URL . "/stock-icons/16x16/trash.png\"/>\n";
                echo "</a></li>\n";
            }
            echo "</ul>\n";
        }
        else
        {
            echo "<p>" . $_MIDCOM->i18n->get_string('no files', 'midgard.admin.asgard') . "</p>\n";
        }
        ?>
    </div>
    <div class="main">
