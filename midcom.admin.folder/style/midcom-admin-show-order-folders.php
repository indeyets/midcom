    <div class="midcom-admin-order" style="display: block;">
        <h3><?php echo $_MIDCOM->i18n->get_string('sort folders', 'midcom.admin.folder'); ?></h3>
        <ul id="midcom_admin_content_folders_list" class="folders sortable">
<?php
foreach ($data['folders'] as $folder)
{
?>
            <li><img src="<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/16x16/folder.png" alt="" /> <input type="hidden" name="midcom_admin_content_folder_score[]" value="&(folder.id);" />&(folder.extra);</li>
<?php
}
?>
        </ul>
        <script type="text/javascript">
            // <!--
                Sortable.create('midcom_admin_content_folders_list');
            // -->
        </script>
    </div>
