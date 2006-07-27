<?php
/* 
  This Style Snippet is static, i.e. in cannot be replaced by a custom
  Style element
*/ 

$rss_title = $this->_config->get("rss_title");
if (! $rss_title) $rss_title = htmlspecialchars($this->_topic->extra);
$rss_link = $server_url.$prefix;
$rss_description = $this->_config->get("rss_description");
$rss_webmaster = $this->_config->get("rss_webmaster");
$rss_language = $this->_config->get("rss_language");

header("Content-type: text/xml; charset=UTF-8");
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?><rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:photo="http://www.pheed.com/pheed/">
  <channel>
    <title><?php echo utf8_encode($rss_title) ?></title>
    <link><?php echo $rss_link ?></link>
    <description><?php echo utf8_encode($rss_description) ?></description>
    <language><?php echo $rss_language ?></language>
    <webMaster><?php echo utf8_encode($rss_webmaster) ?></webMaster>
    <dc:title><?php echo utf8_encode($rss_title) ?></dc:title>
    <generator>Midgard Components Framework - net.siriux.photos</generator>
