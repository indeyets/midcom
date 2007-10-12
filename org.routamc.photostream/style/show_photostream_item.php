<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$view = $data['photo_view'];
$thumbnail = $data['datamanager']->types['photo']->attachments_info['thumbnail'];
$photo_url = "{$prefix}photo/{$data['photo']->guid}/";

if (isset($data['url_suffix']))
{
    $photo_url .= $data['url_suffix'];
}
?>
<li class="photo">
    <?php
    echo "<a href=\"{$photo_url}\"><img src=\"{$thumbnail['url']}\" {$thumbnail['size_line']} alt=\"{$thumbnail['filename']}\" /></a>";
    ?>
    <span class="title">&(view['title']:h);</span>
    <span class="rating">&(view['rating']:h);</span>
</li>