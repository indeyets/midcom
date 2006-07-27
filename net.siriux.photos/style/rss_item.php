<?php
/* 
  This Style Snippet is static, i.e. in cannot be replaced by a custom
  Style element
*/ 
?>
    <item>
        <title><?php echo utf8_encode(htmlspecialchars($view->datamanager->data['title'])) ?></title>
        <link><?php echo $server_url.$prefix.$articles->name ?>.html</link>
        <guid isPermaLink="true"><?php echo $server_url.$prefix.$articles->name ?>.html</guid>
        <pubDate><?php if ($view->datamanager->data['taken']['timestamp']) { echo $view->datamanager->data['taken']['rfc_822']; } ?></pubDate>
        <description><?php echo utf8_encode(htmlspecialchars(strip_tags($view->datamanager->data['description']))) ?></description>
        <content:encoded><?php echo "<!"."[CDATA[".utf8_encode(

preg_replace(',<(a|link|img|script|form|input)([^>]+)(href|src|action)="/([^>"\s]+)",ie',
    '"<\1\2\3=\"$server_url/\4\""', $view->datamanager->data['description'])

)."]]".">" ?></content:encoded>
        <photo:imgsrc><?php echo $attachmentserver.$view->fullscale."/fullscale_".$view->datamanager->data['name'] ?>.jpg</photo:imgsrc>
        <photo:thumbnail><?php echo $attachmentserver.$view->thumbnail."/thumbnail_".$view->datamanager->data['name'] ?>.jpg</photo:thumbnail>
    </item>
