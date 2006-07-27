<?php
/* 
  This Style Snippet is static, i.e. in cannot be replaced by a custom
  Style element
*/ 

$rss_title = $this->_config->get("rss_title");
if (! $rss_title) $rss_title = htmlspecialchars($this->_config_topic->extra);
$rss_link = $prefix;
$rss_description = $this->_config->get("rss_description");
$rss_webmaster = $this->_config->get("rss_webmaster");
$rss_language = $this->_config->get("rss_language");

/* Detect encoding */
$i18n =& $GLOBALS["midcom"]->get_service("i18n");
$encoding = $i18n->get_current_charset();

header("Content-type: text/xml; charset=$encoding");
echo '<?xml version="1.0" encoding="' . $encoding . '"?>' . "\n";
?><rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/">
  <channel>
    <title><?php echo ($rss_title); ?></title>
    <link><?php echo $rss_link; ?></link>
    <description><?php echo ($rss_description); ?></description>
    <?php if ($rss_language) { ?>
      <language><?php echo $rss_language; ?></language>
    <?php } ?>
    <?php if ($rss_webmaster) { ?>
      <webMaster><?php echo ($rss_webmaster); ?></webMaster>
    <?php } ?>
    <dc:title><?php echo ($rss_title); ?></dc:title>
    <generator>Midgard Components Framework - de.linkm.newsticker</generator>
