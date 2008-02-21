<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$view = $data['photo_view'];
$thumbnail = false;
if (isset($data['datamanager']->types['photo']->attachments_info['thumbnail']))
{
    $thumbnail = $data['datamanager']->types['photo']->attachments_info['thumbnail'];
}
$photo_url = "{$prefix}photo/{$data['photo']->guid}/";

if (isset($data['url_suffix']))
{
    $photo_url .= $data['suffix'];
}
?>
<li class="photo">
    <?php
    if ($thumbnail)
    {
        echo "    <a href='{$photo_url}'><img src='{$thumbnail['url']}' {$thumbnail['size_line']} alt='{$thumbnail['filename']}' /></a>\n";
        echo "    <span class='title'>{$view['title']}</span>\n";
    }
    else
    {
        // TODO: Some sort of "broken image" placeholder ??
        echo "    <span class='title'><a href='{$photo_url}'>{$view['title']}</a></span>\n";
    }
    ?>
    <span class="rating">&(view['rating']:h);</span>
</li>