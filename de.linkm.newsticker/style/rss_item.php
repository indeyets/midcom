<?php
/* 
  This Style Snippet is static, i.e. in cannot be replaced by a custom
  Style element
*/ 

$content = "";
// Include schema field "image" to RSS export if available
if (array_key_exists("image", $view) && $view["image"]) {

    if ($GLOBALS["view_rss_include_image"]) {
      // Add full-scale image
      $content .= "\n<p align=\"center\"><img src=\"".$view["image"]["url"]."\" title=\"".$view["image"]["description"]."\" alt=\"".$view["image"]["description"]."\" ".$view["image"]["size_line"]." /></p>\n";

    } elseif (isset($view["image"]["thumbnail"])) {
      // Add only thumbnail
      $view["image"]["url"] = $view["image"]["thumbnail"]["url"];
      $view["image"]["size_line"] = $view["image"]["thumbnail"]["size_line"];
      $content .= "\n<img src=\"".$view["image"]["url"]."\" align=\"right\" title=\"".$view["image"]["description"]."\" alt=\"".$view["image"]["description"]."\" ".$view["image"]["size_line"]." />\n";
    }
}
if (array_key_exists("abstract", $view)) {
    $content .= $view["abstract"];
}
if (array_key_exists("content", $view)) {
    $content .= "\n".$view["content"];
}

// Replace links
$content = preg_replace(',<(a|link|img|script|form|input)([^>]+)(href|src|action)="/([^>"\s]+)",ie',
    '"<\1\2\3=\"$server_url/\4\""', $content);
?>
    <item>
        <dc:subject><?php echo (htmlspecialchars($view["title"])); ?></dc:subject>
        <title><?php echo (htmlspecialchars($view["title"])); ?></title>
        <link><?php echo $prefix.$view_name; ?>.html</link>
        <guid isPermaLink="true"><?php echo $_MIDCOM->get_host_prefix() . "midcom-permalink-{$view['_storage_guid']}"; ?></guid>
        <pubDate><?php echo date('r',$view_date); ?></pubDate>
        <?php if ($GLOBALS["view_author"]->email) { ?>
          <author><?php echo (htmlspecialchars($GLOBALS["view_author"]->email." (".$GLOBALS["view_author"]->name.")")); ?></author>
        <?php } else { ?>
          <dc:creator><?php echo (htmlspecialchars($GLOBALS["view_author"]->name)); ?></dc:creator>
        <?php } ?>
        <description><?php echo (htmlspecialchars(strip_tags($content))); ?></description>
        <content:encoded><?php echo "<!"."[CDATA[".($content)."]]".">"; ?></content:encoded>
<?php
if (array_key_exists("category", $view) && $view["category"]) {
    if (is_array($view["category"])) {
        foreach ($view["category"] as $category) {
	    // Multiple categories
	    ?>
	    <category><?php echo (htmlspecialchars($category)); ?></category>
	    <?php
	}
    } else {
        // Single category
        ?>
        <category><?php echo (htmlspecialchars($view["category"])); ?></category>
        <?php
   } 
} elseif (array_key_exists("categories", $view) && $view["categories"]) {
    if (is_array($view["categories"])) {
        foreach ($view["categories"] as $category) {
            // Multiple categories
            ?>
            <category><?php echo (htmlspecialchars($category)); ?></category>
            <?php
        }
    }
}

// Enclosure for podcasting support etc
if (   array_key_exists('enclosure',$view)
    && $view['enclosure'])
{
    // We might want to optionally define duration of the video or audio file
    $duration = '';
    if (array_key_exists('duration', $view))
    {
        $duration = " duration=\"{$view['duration']}\"";
    }

    echo "<enclosure url=\"{$server_url}{$view['enclosure']['url']}\" length=\"{$view['enclosure']['filesize']}\" type=\"{$view['enclosure']['mimetype']}\"{$duration} />\n";
}
?>
    </item>
