<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
    <div class="midcom-admin-order" style="display: block;">
        <h3><?php echo $_MIDCOM->i18n->get_string('sort by score', 'midcom.admin.folder'); ?></h3>
        <ul id="midcom_admin_content_mixed_list" class="mixed sortable">
<?php
foreach ($data['mixed'] as $id => $name)
{
    $type = explode('_', $id);
    
    if ($type[2] === 'folder')
    {
        $image = 'folder.png';
    }
    else
    {
        $image = 'document.png';
    }
?>
            <li class="&(type[2]);"><img src="<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/16x16/&(image);" alt="" /> <input type="hidden" name="midcom_admin_content_mixed_score[]" value="&(id);" />&(name);</li>
<?php
}
?>
        </ul>
        <script type="text/javascript">
            // <!--
                Sortable.create('midcom_admin_content_mixed_list');
            // -->
        </script>
    </div>