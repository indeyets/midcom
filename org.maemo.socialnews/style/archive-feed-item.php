<?php
$prefix = substr($_MIDCOM->get_host_prefix(), 0, -1) . $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$item = new FeedItem();
$item->descriptionHtmlSyndicated = true;
$item->title = $data['issue']->title;
$item->description = $data['issue']->content;

// Replace links
$item->description = preg_replace(',<(a|link|img|script|form|input)([^>]+)(href|src|action)="/([^>"\s]+)",ie', '"<\1\2\3=\"' . $_MIDCOM->get_host_name() . '/\4\""', $item->description);

$item->date = $data['issue']->metadata->published;
$item->link = "{$prefix}archive/month/" . date('Y/m', $data['issue']->metadata->published) . "/#{$data['issue']->guid}";
$item->guid = "{$prefix}archive/month/" . date('Y/m', $data['issue']->metadata->published) . "/#{$data['issue']->guid}";
$data['feedcreator']->addItem($item);
?>