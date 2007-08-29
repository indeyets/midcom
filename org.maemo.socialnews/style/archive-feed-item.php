<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$item = new FeedItem();
$item->descriptionHtmlSyndicated = true;
$item->title = $data['issue']->title;
$item->description = $data['issue']->content;
$item->date = $data['issue']->metadata->published;
$item->link = "{$prefix}archive/month/" . date('Y/m', $data['issue']->metadata->published) . ".html#{$data['issue']->guid}";
$item->guid = "{$prefix}archive/month/" . date('Y/m', $data['issue']->metadata->published) . ".html#{$data['issue']->guid}";
$data['feedcreator']->addItem($item);
?>