<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$photo_url = "{$prefix}photo/{$data['photo']->guid}/";

$view = $data['photo_view'];
$view_scale = $data['datamanager']->types['photo']->attachments_info['view'];
?>
<div class="org_routamc_photostream_photo">
    <div class="photo">
        <?php
        echo "<a href=\"{$photo_url}\"><img src=\"{$view_scale['url']}\" {$view_scale['size_line']} alt=\"{$view_scale['filename']}\" title=\"{$view_scale['description']}\" /></a>";
        ?>
    </div>
    <h1><?php echo $view['title']; ?></h1>
</div>