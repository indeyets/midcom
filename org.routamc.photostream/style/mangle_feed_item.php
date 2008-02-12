<?php
/**
 * NOTE: Anything you output directly in this element will always be lost since
 * the feed generator tries to protect itself, use debug_add()
 */
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$item =& $data['item'];
$photo =& $data['photo'];

$thumbnail = false;
if (   isset($data['datamanager']->types['photo'])
    && isset($data['datamanager']->types['photo']->attachments_info['thumbnail']))
{
    $thumbnail = $data['datamanager']->types['photo']->attachments_info['thumbnail'];
}

$main = false;
if (   isset($data['datamanager']->types['photo'])
    && isset($data['datamanager']->types['photo']->attachments_info['main']))
{
    $main = $data['datamanager']->types['photo']->attachments_info['main'];
}

// Hack to add 100% custom markup to the item
$add_markup = '';
if ($main)
{
    switch ($data['feed_type'])
    {
        case 'RSS2.0':
        case 'RSS1.0':
        case 'RSS0.91':
            $add_markup .= "<media:content url=\"{$main['url']}\" type=\"{$main['mimetype']}\" {$main['size_line']} />\n";
            $add_markup .= '<media:title>' . htmlentities($item->title) . "</media:title>\n";
            $add_markup .= '<media:text type="html">' . htmlentities($item->description) . "</media:text>\n";
            if ($thumbnail)
            {
                $add_markup .= "<media:thumbnail url=\"{$thumbnail['url']}\" {$thumbnail['size_line']} />\n";
            }
            if (!empty($data['tags']))
            {
                $add_markup .= '<media:category scheme="urn:flickr:tags">' . htmlentities(net_nemein_tag_handler::tag_array2string($data['tags'])) . "</media:category>\n";
            }
            break;
        case 'ATOM':
            $add_markup .= "<link rel=\"enclosure\" type=\"{$main['mimetype']}\" href=\"{$main['url']}\" />\n";
            if (!empty($data['tags']))
            {
                foreach ($data['tags'] as $tag => $dummy)
                {
                    $add_markup .= '<category term="' . htmlentities($tag) . '" scheme="http://www.flickr.com/photos/tags/" />' . "\n";
                }
            }
            break;
    }
}


if ($add_markup)
{
    $item->additionalElements['remove_tag_but_not_contents'] = $add_markup;
}


//dc:date.Taken should work for all feeds 
$date = new FeedDate($item->date);
$item->additionalElements['dc:date.Taken'] = $date->iso8601();

?>