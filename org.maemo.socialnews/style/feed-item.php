<?php
$prefix = substr($_MIDCOM->get_host_prefix(), 0, -1) . $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$item = new FeedItem();
$item->descriptionHtmlSyndicated = true;
$item->title = $data['article']->title;
$item->description = $data['article']->content;
$item->description .= net_nemein_favourites_admin::render_add_link($data['article']->__new_class_name__, $data['article']->guid);

// Replace links
$item->description = preg_replace(',<(a|link|img|script|form|input)([^>]+)(href|src|action)="/([^>"\s]+)",ie', '"<\1\2\3=\"' . $_MIDCOM->get_host_name() . '/\4\""', $item->description);

$item->date = $data['article']->metadata->published;
$item->link = $data['article']->url;
$item->guid = $data['article']->url;
$data['feedcreator']->addItem($item);
?>