<?php
$item = new FeedItem();
$item->descriptionHtmlSyndicated = true;
$authors = explode('|', $data['event']->metadata->authors);
$author_user = $_MIDCOM->auth->get_user($authors[1]);
if ($author_user)
{
    $author = $author_user->get_storage();
    
    if (empty($author->email))
    {
        $author->email = "webmaster@{$_SERVER['SERVER_NAME']}";
    }
    
    $item->author = trim("{$author->name} <{$author->email}>");
}

$item->title = sprintf('%s: %s', strftime('%x', strtotime($data['event']->start)), $data['event']->title);
$arg = $data['event']->name ? $data['event']->name : $data['event']->guid;
$item->link = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "{$arg}/";

// Add xCal data to item
$item->additionalElements['xcal:dtstart'] = $data['event']->start . 'Z';
$item->additionalElements['xcal:dtend'] = $data['event']->end . 'Z';

$item->guid = $_MIDCOM->permalinks->create_permalink($data['event']->guid);
$item->date = $data['event']->metadata->published;
$item->description = '';

if (   array_key_exists('image', $data['datamanager']->types)
    && $data['config']->get('rss_use_image'))
{
    $item->description .= "\n<div class=\"image\">" . $data['datamanager']->types['image']->convert_to_html() .'</div>';
}

if ($data['config']->get('rss_use_content'))
{
    $item->description .= "\n" . $data['datamanager']->types['description']->convert_to_html();
}

// Replace links
$item->description = preg_replace(',<(a|link|img|script|form|input)([^>]+)(href|src|action)="/([^>"\s]+)",ie', '"<\1\2\3=\"' . $_MIDCOM->get_host_name() . '/\4\""', $item->description);

if ($GLOBALS['midcom_config']['positioning_enable'])
{
    $_MIDCOM->load_library('org.routamc.positioning');
    
    // Attach coordinates to the item if available
    $object_position = new org_routamc_positioning_object($data['event']);
    $coordinates = $object_position->get_coordinates();
    if (!is_null($coordinates))
    {
        $item->lat = $coordinates['latitude'];
        $item->long = $coordinates['longitude'];
    }
}

$data['feedcreator']->addItem($item);
?>