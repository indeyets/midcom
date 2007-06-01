<script type="text/javascript">
    // <!--
        function disable_sorting()
        {
            document.getElementById('midcom_admin_folder_show_save_info').style.display = 'block';
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'f_submit';
            input.value = 'submit';
            
            document.getElementById('midcom_admin_folder_order_form').appendChild(input);
            document.getElementById('midcom_admin_folder_order_form').submit();
        }
    // -->
</script>
<h1><?php echo $data['title']; ?></h1>
<form method="post" action="" enctype="multipart/form-data" id="midcom_admin_folder_order_form" class="datamanager midcom_admin_folder sort_folder">
    <label for="midcom_admin_content_order_navorder">
        <select name="f_navorder" id="midcom_admin_folder_order_navorder" onChange="javascript:disable_sorting();">
<?php
foreach ($data['navorder_list'] as $key => $value)
{
    if ($key == $data['navorder'])
    {
        $selected = ' selected="selected"';
    }
    else
    {
        $selected = '';
    }
?>
            <option value="&(key);"&(selected:h);>&(value:h);</option>
<?php
}
?>
        </select>
    </label>
    <p id="midcom_admin_folder_show_save_info" style="">
        <?php echo $_MIDCOM->i18n->get_string('saving changes', 'midcom.admin.folder'); ?>
    </p>
