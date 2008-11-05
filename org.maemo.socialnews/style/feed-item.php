<?php
$prefix = substr($_MIDCOM->get_host_prefix(), 0, -1) . $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$date_string = "<abbr class=\"published\" title=\"" . strftime('%Y-%m-%dT%H:%M:%S%z', $data['article']->metadata->published) . "\">" . gmdate('Y-m-d H:i e', $data['article']->metadata->published) . "</abbr>";
$item = new FeedItem();
$item->descriptionHtmlSyndicated = true;
$item->title = $data['article']->title;
$item->description = $data['article']->content;
$item->description .= net_nemein_favourites_admin::get_add_link($data['article']->__mgdschema_class_name__, $data['article']->guid);

$item->description .= "<div class=\"org_maemo_socialnews_score\">\n";
$item->description .= sprintf($data['l10n']->get('%s with score %d'), $date_string, $data['score']);
if (isset($data['attention']))
{
    $item->description .= " " . sprintf($data['l10n']->get('your attention: %s%%'), round($data['attention'] * 100));
}
$item->description .= "</div>\n";

// Replace links
$item->description = preg_replace(',<(a|link|img|script|form|input)([^>]+)(href|src|action)="/([^>"\s]+)",ie', '"<\1\2\3=\"' . $_MIDCOM->get_host_name() . '/\4\""', $item->description);

$item->date = $data['article']->metadata->published;
$item->link = $data['article']->url;
$item->guid = $data['article']->url;
$data['feedcreator']->addItem($item);
?>