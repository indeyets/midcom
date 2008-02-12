<?php
/**
 * NOTE: Anything you output directly in this element will always be lost since
 * the feed generator tries to protect itself, use debug_add()
 */
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$feed_data =& $data['feed_data'];
// Remove special elements possibly added by mangle_feed_item
$feed_data = preg_replace('%</?remove_tag_but_not_contents>%', '', $feed_data);
switch ($data['feed_type'])
{
    case 'RSS2.0':
    case 'RSS1.0':
    case 'RSS0.91':
        // add DC namespace
        $feed_data = preg_replace('%<rss(.*?)>%s', "<rss\\1\n     xmlns:dc=\"http://purl.org/dc/elements/1.1/\">", $feed_data);
        // add media namespace
        $feed_data = preg_replace('%<rss(.*?)>%s', "<rss\\1\n     xmlns:media=\"http://search.yahoo.com/mrss/\">", $feed_data);
        break;
    case 'ATOM':
        // add DC namespace
        $feed_data = preg_replace('%<feed(.*?)>%s', "<feed\\1\n      xmlns:dc=\"http://purl.org/dc/elements/1.1/\">", $feed_data);
        break;
}

?>