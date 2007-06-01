    <div class="midcom-admin-order" style="display: block;">
<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
        <h3><?php echo $_MIDCOM->i18n->get_string('sort pages', 'midcom.admin.folder'); ?></h3>
        <ul id="midcom_admin_content_pages_list" class="pages sortable">
<?php
foreach ($data['pages'] as $page)
{
?>
            <li><img src="<?php echo MIDCOM_STATIC_URL; ?>/stock-icons/16x16/new-text.png" alt="" /> <input type="hidden" name="midcom_admin_content_page_score[]" value="&(page.id);" />&(page.title);</li>
<?php
}
?>
        </ul>
        <script type="text/javascript">
            // <!--
                Sortable.create('midcom_admin_content_pages_list');
            // -->
        </script>
    </div>