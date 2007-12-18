<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$feedtopic = (int)$data['topic']->feedtopic;
$feedtopic_guid = $data['topic']->guid;
$categories_str = '';
if($data['topic']->categories != '||' && $data['topic']->categories != '')
{
    $categories = explode('|', $data['topic']->categories);
    $categories_count = count($categories);
    $categories_str = $data['l10n']->get('categories') . ": ";
    foreach($categories as $key => $category)
    {
        if (     $key == 0 
            ||  $key == $categories_count - 1
           )
        {
            continue;
        }
        if ( $key == $categories_count - 2 )
        {
            $categories_str .= $category;
        }
        else
        {
            $categories_str .= $category . ", ";
        }
    }
    $categories_str .= '';
}

$topic = new midcom_db_topic($feedtopic);
$url = $data['permalinks']->create_permalink($topic->guid);
$static_url = MIDCOM_STATIC_URL;
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<li style="clear: both";>
    <div class="net_nemein_feedcollector_management_topic">
        <div class="net_nemein_feedcollector_management_topic_buttons" style="float:right;">
            <a href="&(prefix);__ais/folder/metadata/&(feedtopic_guid);.html" title="<? echo $data['l10n']->get('edit metadata'); ?>" alt="<? echo $data['l10n']->get('edit metadata'); ?>"><img src="&(static_url);/stock-icons/16x16/metadata.png" alt="<? echo $data['l10n']->get('edit metadata'); ?>" border="0"></a>
            <a href="&(prefix);manage/edit/&(feedtopic_guid);" title="<? echo $data['l10n']->get('edit'); ?>" alt="<? echo $data['l10n']->get('edit'); ?>"><img src="&(static_url);/stock-icons/16x16/edit.png" alt="<? echo $data['l10n']->get('edit'); ?>" border="0"></a>
            <a href="&(prefix);manage/delete/&(feedtopic_guid);" title="<? echo $data['l10n']->get('remove'); ?>" alt="<? echo $data['l10n']->get('remove'); ?>"><img src="&(static_url);/stock-icons/16x16/cancel.png" alt="<? echo $data['l10n']->get('remove'); ?>" border="0"></a>
        </div>
        <a href="&(url);"><?php echo $data['topic']->title; ?></a>&nbsp;&(categories_str);
    </div>
</li>
