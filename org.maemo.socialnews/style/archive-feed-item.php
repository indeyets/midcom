<?php
$item = new FeedItem();
$item->descriptionHtmlSyndicated = true;
$item->title = $data['issue']->title;
$item->description = $data['issue']->content;
$item->date = $data['issue']->metadata->published;
// TODO: Link
$data['feedcreator']->addItem($item);
?>