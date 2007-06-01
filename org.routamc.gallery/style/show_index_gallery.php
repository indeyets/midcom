<?php
$data = & $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$view = $data['photo_view'];
$thumbnail = $data['datamanager']->types['photo']->attachments_info['thumbnail'];
$photo_url = $data['gallery'][MIDCOM_NAV_FULLURL];
?>
<li class="gallery">
    <?php
    echo "<a href=\"{$photo_url}\"><img src=\"{$thumbnail['url']}\" {$thumbnail['size_line']} alt=\"{$thumbnail['filename']}\" /></a>";
    ?>
    <span class="title"><a href="&(photo_url);"><?php echo $data['gallery'][MIDCOM_NAV_NAME]; ?></a></span>
</li>